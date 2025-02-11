<?php

// src/Payment/PaypalPaymentProcessorAdapter.php
namespace App\Payment;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private PaypalPaymentProcessor $paypalProcessor)
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
            $this->paypalProcessor->pay($priceInCents);
            return true; // Если не было исключения
        } catch (\Exception $e) {
            // Можно залогировать ошибку
            return false;
        }
    }
}
