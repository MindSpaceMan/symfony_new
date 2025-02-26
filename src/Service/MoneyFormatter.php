<?php

declare(strict_types=1);

namespace App\Service;

use Brick\Money\Money;

final class MoneyFormatter
{
    /**
     * Форматирует `Money` в `float`
     */
    public function format(Money $money): float
    {
        return $money->getAmount()->toFloat();
    }
}
