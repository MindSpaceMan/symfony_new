<?php

// src/Payment/PaymentProcessorInterface.php
namespace App\Payment;

interface PaymentProcessorInterface
{
    public function pay(float $amount): bool;
}
