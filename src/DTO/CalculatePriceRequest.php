<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ValidTaxNumber;

final class CalculatePriceRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private int $product;

    #[Assert\NotBlank]
    #[ValidTaxNumber]
    private string $taxNumber;

    #[Assert\Type('string')]
    private ?string $couponCode = null;


    public function getProduct(): int
    {
        return $this->product;
    }

    public function setProduct(int $product): void
    {
        $this->product = $product;
    }

    public function getTaxNumber(): string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function setCouponCode(?string $couponCode): void
    {
        $this->couponCode = $couponCode;
    }

}
