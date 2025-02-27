<?php

declare(strict_types=1);

namespace App\Service;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Math\RoundingMode;
use DomainException;
use App\Entity\Product;
use App\Entity\Coupon;

final class PriceCalculator
{
    /**
     * Рассчитывает финальную цену продукта с учетом купона и налога.
     *
     * @param Product $product
     * @param Coupon|null $coupon
     * @param string $taxNumber
     * @return BigDecimal
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     */
    public function calculatePrice(Product $product, ?Coupon $coupon, string $taxNumber): BigDecimal
    {
        // Предполагаем, что цена продукта хранится в минорных единицах (например, центах)
        $basePrice = BigDecimal::of($product->getPrice());

        if ($coupon) {
            $basePrice = $this->applyCoupon($basePrice, $coupon);
        }

        $taxRate = $this->getTaxRate($taxNumber);

        // Рассчитываем налог: налог = базовая цена * ставка налога (с округлением)
        $tax = $basePrice->multipliedBy($taxRate, RoundingMode::HALF_UP);

        // Финальная цена = базовая цена + налог
        return $basePrice->plus($tax);
    }

    /**
     * Применяет купон к базовой цене.
     *
     * @throws RoundingNecessaryException
     */
    private function applyCoupon(BigDecimal $price, Coupon $coupon): BigDecimal
    {
        return match ($coupon->getDiscountType()) {
            'fixed' => $this->applyFixedDiscount($price, $coupon->getValue()),
            'percent' => $this->applyPercentageDiscount($price, $coupon->getValue()),
            default => throw new DomainException("Unknown discount type: {$coupon->getDiscountType()}"),
        };
    }

    /**
     * Применяет фиксированную скидку.
     *
     * @param BigDecimal $price Базовая цена в минорных единицах.
     * @param int $discountValue Фиксированная скидка в минорных единицах.
     *
     * @return BigDecimal
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     */
    private function applyFixedDiscount(BigDecimal $price, int $discountValue): BigDecimal
    {
        $result = $price->minus(BigDecimal::of($discountValue));
        // Гарантируем, что итоговая цена не опустится ниже 1 (т.е. 0.01 евро)
        return $result->isLessThan(BigDecimal::of(1)) ? BigDecimal::of(1) : $result;
    }

    /**
     * Применяет процентную скидку, гарантируя, что процент ≤ 100%.
     *
     * @param BigDecimal $price Базовая цена в минорных единицах.
     * @param int $discountValue Процент скидки (целое число от 0 до 100).
     *
     * @return BigDecimal
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     */
    private function applyPercentageDiscount(BigDecimal $price, int $discountValue): BigDecimal
    {
        if ($discountValue > 100) {
            throw new DomainException("Discount percentage cannot be greater than 100%");
        }

        // Вычисляем множитель: (1 - скидка/100)
        $multiplier = BigDecimal::of(1)
            ->minus(BigDecimal::of($discountValue)->dividedBy(100, 2));

        $result = $price->multipliedBy($multiplier, RoundingMode::HALF_UP);
        return $result->isLessThan(BigDecimal::of(1)) ? BigDecimal::of(1) : $result;
    }

    /**
     * Определяет ставку налога по префиксу налогового номера.
     */
    private function getTaxRate(string $taxNumber): BigDecimal
    {
        $prefix = substr($taxNumber, 0, 2);

        return match ($prefix) {
            'DE' => BigDecimal::of('0.19'),
            'IT' => BigDecimal::of('0.22'),
            'FR' => BigDecimal::of('0.20'),
            'GR' => BigDecimal::of('0.24'),
            default => throw new DomainException("Unknown tax number prefix: {$prefix}"),
        };
    }
}
