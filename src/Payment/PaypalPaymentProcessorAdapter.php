<?php

// src/Payment/PaypalPaymentProcessorAdapter.php
namespace App\Payment;

class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private PaypalPaymentProcessor $paypalProcessor)
    {
    }

    public function pay(float $amount): bool
    {
        return $this->paypalProcessor->pay($amount);
    }
}
