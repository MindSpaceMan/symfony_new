<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CouponRepository;
use App\Entity\Coupon;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class CouponService
{
    public function __construct(private readonly CouponRepository $couponRepo) {}

    /**
     * Получает купон, если он существует, иначе выбрасывает исключение.
     */
    public function getCoupon(?string $couponCode): ?Coupon
    {
        if (!$couponCode) {
            return null;
        }

        $coupon = $this->couponRepo->findOneBy(['code' => $couponCode]);
        if (!$coupon) {
            throw new UnprocessableEntityHttpException("Coupon not found: $couponCode");
        }

        return $coupon;
    }
}
