<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidTaxNumber extends Constraint
{
    public readonly string $message;

    public function __construct(string $message = 'Неверный формат налогового номера: "{{ value }}"')
    {
        parent::__construct($message);

        $this->message = $message;
    }
    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
