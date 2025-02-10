<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;

class PriceCalculator
{
    public function calculatePrice(Product $product, ?Coupon $coupon, string $taxNumber): float
    {
        $basePrice = $product->getPrice();

        // Применить купон, если есть
        if ($coupon) {
            $basePrice = $this->applyCoupon($basePrice, $coupon);
        }

        // Определить налог
        $taxRate = $this->getTaxRate($taxNumber);

        // Прибавить налог
        $finalPrice = $basePrice + ($basePrice * $taxRate);

        return round($finalPrice, 2);
    }

    private function applyCoupon(float $price, Coupon $coupon): float
    {
        if ($coupon->getDiscountType() === 'fixed') {
            return max(0, $price - $coupon->getValue());
        } else { // 'percent'
            return $price * (1 - $coupon->getValue() / 100);
        }
    }

    private function getTaxRate(string $taxNumber): float
    {
        $prefix = substr($taxNumber, 0, 2);
        return match ($prefix) {
            'DE' => 0.19,
            'IT' => 0.22,
            'FR' => 0.20,
            'GR' => 0.24,
            default => 0.0,
        };
    }
}
