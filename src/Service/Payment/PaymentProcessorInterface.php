<?php

namespace App\Service\Payment;

interface PaymentProcessorInterface
{
    public function pay(float $amount): bool;
}
