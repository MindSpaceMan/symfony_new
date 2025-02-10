<?php

// src/Service/PaymentService.php
namespace App\Service;

use App\Payment\PaymentProcessorInterface;

class PaymentService
{
    public function __construct(private readonly PaymentProcessorInterface $paymentProcessor)
    {
    }

    public function pay(float $amount): bool
    {
        return $this->paymentProcessor->pay($amount);
    }
}
