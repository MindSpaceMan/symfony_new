<?php

// CalculatePriceRequest.php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ValidTaxNumber;

class CalculatePriceRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private mixed $product;

    #[Assert\NotBlank]
    #[ValidTaxNumber]
    private mixed $taxNumber;

    #[Assert\Type('string')]
    private mixed $couponCode;

    public function getProduct(): mixed
    {
        return $this->product;
    }

    public function setProduct(mixed $product): void
    {
        $this->product = $product;
    }

    public function getTaxNumber(): mixed
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(mixed $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    public function getCouponCode(): mixed
    {
        return $this->couponCode;
    }

    public function setCouponCode(mixed $couponCode): void
    {
        $this->couponCode = $couponCode;
    }
    // геттеры/сеттеры...


}
