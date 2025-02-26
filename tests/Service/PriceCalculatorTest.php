<?php

namespace App\Tests\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\PriceCalculator;
use Brick\Math\Exception\RoundingNecessaryException;
use PHPUnit\Framework\TestCase;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    /**
     * Проверяем простой расчёт цены без купона и без налога
     * (Допустим, если taxNumber неизвестной страны, налог = 0)
     */
    public function testCalculatePrice_NoTaxNoCoupon(): void
    {
        $product = (new Product())
            ->setName('TestProduct')
            ->setPrice(100);

        $finalPrice = $this->calculator->calculatePrice($product, null, 'XX123');

        $this->assertSame(100.0, $finalPrice->getAmount()->toFloat(), 'Цена должна остаться без изменений.');
    }

    /**
     * Проверяем налог для Германии (19%)
     */
    public function testCalculatePrice_GermanyTax(): void
    {
        $product = (new Product())
            ->setName('TestProduct')
            ->setPrice(100);

        $finalPrice = $this->calculator->calculatePrice($product, null, 'DE123456789');

        $this->assertSame(119.0, $finalPrice->getAmount()->toFloat(), 'Цена должна быть 119€ (100€ + 19%).');
    }

    /**
     * Проверяем применение фиксированной скидки (coupon = "D15") на продукт
     * и налог в Италии (22%)
     * @throws RoundingNecessaryException
     */
    public function testCalculatePrice_FixedDiscount_Italy(): void
    {
        $product = (new Product())
            ->setName('Iphone')
            ->setPrice(100);

        // Фиксированная скидка 15 (евро)
        $coupon = (new Coupon())
            ->setCode('D15')
            ->setDiscountType('fixed')
            ->setValue(15);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'IT12345678900');

        // Изначально 100 - 15 = 85, затем налог 22% -> 85 + 85*0.22 = 103.7
        $expected = Money::of(103.7, 'EUR')->getAmount()->toScale(2, RoundingMode::HALF_UP)->toFloat();
        $this->assertSame($expected, $finalPrice->getAmount()->toFloat(), 'Ожидаемая цена: 103.70€.');
    }

    /**
     * Проверяем применение процентной скидки (coupon = "P10") и налог 24% (Греция)
     */
    public function testCalculatePrice_PercentDiscount_Greece(): void
    {
        $product = (new Product())->setName('Headphones')->setPrice(20);

        $coupon = (new Coupon())->setCode('P10')->setDiscountType('percent')->setValue(10);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'GR123456789');

        $expected = Money::of(22.32, 'EUR')->getAmount()->toScale(2, RoundingMode::HALF_UP)->toFloat();
        $this->assertSame($expected, $finalPrice->getAmount()->toFloat(), 'Ожидаемая цена: 22.32€.');
    }

    /**
     * Проверяем, что при 100% скидке цена не становится отрицательной.
     */
    public function testCalculatePrice_100PercentCoupon(): void
    {
        $product = (new Product())->setName('Case')->setPrice(10);

        $coupon = (new Coupon())->setCode('P100')->setDiscountType('percent')->setValue(100);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'DE123456789');
        // 10 -> скидка 100% -> 0, налог DE: 19% от 0 = 0
        $this->assertSame(0.0, $finalPrice->getAmount()->toFloat(), 'Цена после 100% скидки должна быть 0€.');
    }

    public function testCalculatePrice_UnknownTaxNumber(): void
    {
        $product = (new Product())->setName('Case')->setPrice(10);

        $this->expectException(\DomainException::class);
        $this->calculator->calculatePrice($product, null, 'UNKNOWN');
    }
}
