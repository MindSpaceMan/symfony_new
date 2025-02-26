<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use Brick\Math\RoundingMode;
use DomainException;

final class PriceCalculator
{
    /**
     * @throws RoundingNecessaryException
     * @throws MathException
     * @throws UnknownCurrencyException
     * @throws NumberFormatException|MoneyMismatchException
     */
    public function calculatePrice(Product $product, ?Coupon $coupon, string $taxNumber): Money
    {
        $basePrice = Money::ofMinor($product->getPrice(), 'EUR');

        if ($coupon) {
            $basePrice = $this->applyCoupon($basePrice, $coupon);
        }

        $taxRate = $this->getTaxRate($taxNumber);

        $tax = $basePrice->multipliedBy($taxRate);
        $tax = Money::of(
            $tax->getAmount()->toScale(2, RoundingMode::HALF_UP),
            $basePrice->getCurrency()
        );

        return Money::of(
            $basePrice->plus($tax)->getAmount()->toScale(2, RoundingMode::HALF_UP),
            $basePrice->getCurrency()
        );
    }

    /**
     * @throws RoundingNecessaryException
     * @throws MoneyMismatchException
     * @throws MathException
     * @throws UnknownCurrencyException
     * @throws NumberFormatException
     */
    private function applyCoupon(Money $price, Coupon $coupon): Money
    {
        return match ($coupon->getDiscountType()) {
            'fixed' => $this->applyFixedDiscount($price, $coupon->getValue()),
            'percent' => $this->applyPercentageDiscount($price, $coupon->getValue()),
            default => throw new DomainException("Unknown discount type: {$coupon->getDiscountType()}"),
        };
    }

    /**
     * Применяет фиксированную скидку.
     */
    private function applyFixedDiscount(Money $price, int $discountValue): Money
    {
        return $price->minus(Money::ofMinor($discountValue, 'EUR'))->max(Money::ofMinor(1, 'EUR')); // Минимум 0.01€
    }

    /**
     * Применяет процентную скидку, гарантируя, что процент ≤ 100%.
     *
     * @param Money $price
     * @param int $discountValue
     * @return Money
     * @throws MathException
     * @throws MoneyMismatchException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    private function applyPercentageDiscount(Money $price, int $discountValue): Money
    {
        if ($discountValue > 100) {
            throw new DomainException("Discount percentage cannot be greater than 100%");
        }

        return $price->multipliedBy(1 - ($discountValue / 100), RoundingMode::HALF_UP)
            ->max(Money::ofMinor(1, 'EUR')); // Минимум 0.01€
    }

    private function getTaxRate(string $taxNumber): float
    {
        $prefix = substr($taxNumber, 0, 2);

        return match ($prefix) {
            'DE' => 0.19,
            'IT' => 0.22,
            'FR' => 0.20,
            'GR' => 0.24,
            default => throw new DomainException("Unknown tax number prefix: {$prefix}"),
        };
    }
}
