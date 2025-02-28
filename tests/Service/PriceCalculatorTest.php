<?php

namespace App\Tests\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\PriceCalculator;
use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
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
            ->setPrice(10000);

        $finalPrice = $this->calculator->calculatePrice($product, null, 'XX123');

        $this->assertSame(10000, $finalPrice->toInt(), 'Цена должна остаться без изменений.');
    }

    /**
     * Проверяем налог для Германии (19%)
     */
    public function testCalculatePrice_GermanyTax(): void
    {
        $product = (new Product())
            ->setName('TestProduct')
            ->setPrice(10000);

        $finalPrice = $this->calculator->calculatePrice($product, null, 'DE123456789');

        $this->assertSame(11900, $finalPrice->toInt(), 'Цена должна быть 119€ (100€ + 19%).');
    }

    /**
     * Проверяем применение фиксированной скидки (coupon = "D15") на продукт
     * и налог в Италии (22%)
     * @throws RoundingNecessaryException
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     */
    public function testCalculatePrice_FixedDiscount_Italy(): void
    {
        $product = (new Product())
            ->setName('Iphone')
            ->setPrice(10000);

        // Фиксированная скидка 15 (евро)
        $coupon = (new Coupon())
            ->setCode('D15')
            ->setDiscountType('fixed')
            ->setValue(1500);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'IT12345678900');

        // Расчёт: 10000 - 1500 = 8500; налог IT (22%) = 8500 * 0.22 = 1870; итого 8500 + 1870 = 10370.
        $this->assertSame(10370, $finalPrice->toInt(), 'Ожидаемая цена: 10370.');
    }

    /**
     * Проверяем применение процентной скидки (coupon = "P10") и налог 24% (Греция)
     */
    public function testCalculatePrice_PercentDiscount_Greece(): void
    {
        $product = (new Product())->setName('Headphones')->setPrice(2000);

        $coupon = (new Coupon())->setCode('P10')->setDiscountType('percent')->setValue(1000);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'GR123456789');

        // Расчёт: 2000 * 0.9 = 1800; налог GR (24%) = 1800 * 0.24 = 432; итого 1800 + 432 = 2232.
        $this->assertSame(2232, $finalPrice->toInt(), 'Ожидаемая цена: 2232.');
    }

    /**
     * Проверяем, что при 100% скидке цена не становится отрицательной.
     */
    public function testCalculatePrice_100PercentCoupon(): void
    {
        $product = (new Product())->setName('Case')->setPrice(1000);

        $coupon = (new Coupon())->setCode('P100')->setDiscountType('percent')->setValue(10000);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'DE123456789');
        // 10 -> скидка 100% -> 0, налог DE: 19% от 0 = 0
        $this->assertSame(0, $finalPrice->toInt(), 'Цена после 100% скидки должна быть 0€.');
    }

    public function testCalculatePrice_UnknownTaxNumber(): void
    {
        $product = (new Product())->setName('Case')->setPrice(1000);

        $this->expectException(\DomainException::class);
        $this->calculator->calculatePrice($product, null, 'UNKNOWN');
    }
}
