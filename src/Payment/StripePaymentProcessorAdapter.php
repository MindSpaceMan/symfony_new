<?php

// src/Payment/StripePaymentProcessorAdapter.php
namespace App\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private StripePaymentProcessor $stripeProcessor)
    {
    }

    /**
     * @param float $amount Сумма в условных единицах (например, евро)
     * @return bool true, если оплата прошла успешно, false — если выброшено исключение
     */
    public function pay(float $amount): bool
    {
        // Вендорский класс принимает сумму в "центах" как int
        $priceInCents = (int) round($amount * 100);

        try {
            $this->stripeProcessor->processPayment($priceInCents);
            return true; // Если не было исключения
        } catch (\Exception $e) {
            // Можно залогировать ошибку
            return false;
        }
    }
}
