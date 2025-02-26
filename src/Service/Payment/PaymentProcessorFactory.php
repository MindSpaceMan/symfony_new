<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class PaymentProcessorFactory
{
    public function __construct(
        private readonly PaymentService $paymentServicePaypal,
        private readonly PaymentService $paymentServiceStripe
    ) {}

    public function getProcessor(string $type): PaymentService
    {
        return match ($type) {
            'paypal' => $this->paymentServicePaypal,
            'stripe' => $this->paymentServiceStripe,
            default => throw new UnprocessableEntityHttpException("Unknown payment processor: $type"),
        };
    }
}
