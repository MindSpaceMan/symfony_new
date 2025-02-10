<?php

// PurchaseRequest.php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ValidTaxNumber;

class PurchaseRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private int $product;

    #[Assert\NotBlank]
    #[ValidTaxNumber]
    private string $taxNumber;

    #[Assert\Type('string')]
    private ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['paypal', 'stripe'], message: 'Unknown payment processor')]
    private string $paymentProcessor;

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

    public function getPaymentProcessor(): string
    {
        return $this->paymentProcessor;
    }

    public function setPaymentProcessor(string $paymentProcessor): void
    {
        $this->paymentProcessor = $paymentProcessor;
    }
}
