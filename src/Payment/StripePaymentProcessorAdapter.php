<?php

// src/Payment/StripePaymentProcessorAdapter.php
namespace App\Payment;

class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private StripePaymentProcessor $stripeProcessor)
    {
    }

    public function pay(float $amount): bool
    {
        return $this->stripeProcessor->processPayment($amount);
    }
}
