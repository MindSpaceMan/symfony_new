<?php
declare(strict_types=1);

namespace App\Enum;

enum PaymentProcessor: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';

    public static function values(): array
    {
        return array_map(fn(self $processor) => $processor->value, self::cases());
    }
}