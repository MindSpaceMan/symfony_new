<?php

// ValidTaxNumber.php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidTaxNumber extends Constraint
{
    public string $message = 'Неверный формат налогового номера: "{{ value }}"';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
