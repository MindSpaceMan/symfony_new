<?php
declare(strict_types=1);

namespace App\DTO;

use App\Enum\PaymentProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ValidTaxNumber;

final class PurchaseRequest
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
    #[Assert\Choice(callback: [PaymentProcessor::class, 'values'], message: 'Unknown payment processor')]
    private string $paymentProcessor;

    public function getProduct(): int
    {
        return $this->product;
    }

    public function getTaxNumber(): string
    {
        return $this->taxNumber;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getPaymentProcessor(): string
    {
        return $this->paymentProcessor;
    }
}
