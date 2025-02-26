<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidTaxNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $patterns = [
            '/^DE\d{9}$/',           // Germany
            '/^IT\d{11}$/',          // Italy
            '/^GR\d{9}$/',           // Greece
            '/^FR[a-zA-Z]{2}\d{9}$/' // France
        ];

        $valid = !empty(array_filter($patterns, fn($pattern) => preg_match($pattern, $value)));

        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
