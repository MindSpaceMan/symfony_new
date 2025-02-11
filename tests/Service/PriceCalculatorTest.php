<?php

namespace App\Tests\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\PriceCalculator;
use PHPUnit\Framework\TestCase;

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
        // Без купона и неизвестный налог -> цена = 100
        $this->assertEquals(100.0, $finalPrice);
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
        // Цена = 100 + 19% = 119
        $this->assertEquals(119.0, $finalPrice);
    }

    /**
     * Проверяем применение фиксированной скидки (coupon = "D15") на продукт
     * и налог в Италии (22%)
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
        $this->assertEquals(103.70, $finalPrice);
    }

    /**
     * Проверяем применение процентной скидки (coupon = "P10") и налог 24% (Греция)
     */
    public function testCalculatePrice_PercentDiscount_Greece(): void
    {
        $product = (new Product())
            ->setName('Headphones')
            ->setPrice(20);

        $coupon = (new Coupon())
            ->setCode('P10')
            ->setDiscountType('percent')
            ->setValue(10);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'GR123456789');
        // Базовая цена 20, скидка 10% -> 18, + 24% налог = 18 + 18*0.24 = 22.32
        $this->assertEquals(22.32, $finalPrice);
    }

    /**
     * Проверяем, что при 100% скидке цена не становится отрицательной.
     */
    public function testCalculatePrice_100PercentCoupon(): void
    {
        $product = (new Product())
            ->setName('Case')
            ->setPrice(10);

        $coupon = (new Coupon())
            ->setCode('P100')
            ->setDiscountType('percent')
            ->setValue(100);

        $finalPrice = $this->calculator->calculatePrice($product, $coupon, 'DE123456789');
        // 10 -> скидка 100% -> 0, налог DE: 19% от 0 = 0
        $this->assertEquals(0.0, $finalPrice);
    }
}
