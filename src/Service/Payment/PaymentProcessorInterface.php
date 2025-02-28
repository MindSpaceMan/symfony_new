<?php

namespace App\Service\Payment;

interface PaymentProcessorInterface
{
    public function pay(int $priceInCents): bool;
}
