<?php
declare(strict_types=1);

namespace App\Service\Payment;

final class PaymentService
{
    public function __construct(private readonly PaymentProcessorInterface $paymentProcessor)
    {
    }

    public function pay(int $priceInCents): bool
    {
        return $this->paymentProcessor->pay($priceInCents);
    }
}
