<?php

declare(strict_types=1);

namespace App\Service;

use Brick\Math\BigDecimal;

final class MoneyFormatter
{
    /**
     * Форматирует `Money` в `float`
     */
    public function format(BigDecimal $money): int
    {
        return $money->toInt();
    }
}
