<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Payment\PaymentProcessorInterface;

final class PaymentService
{
    public function __construct(private readonly PaymentProcessorInterface $paymentProcessor)
    {
    }

    public function pay(int $amount): bool
    {
        return $this->paymentProcessor->pay($amount);
    }
}
