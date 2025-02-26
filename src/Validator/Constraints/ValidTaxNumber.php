<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidTaxNumber extends Constraint
{
    public string $message = 'Неверный формат налогового номера: "{{ value }}"';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
