<?php
declare(strict_types=1);

namespace App\Service;

use App\Payment\PaymentProcessorInterface;

final class PaymentService
{
    public function __construct(private readonly PaymentProcessorInterface $paymentProcessor)
    {
    }

    public function pay(float $amount): bool
    {
        return $this->paymentProcessor->pay($amount);
    }
}
