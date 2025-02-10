<?php

// ValidTaxNumberValidator.php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidTaxNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
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

        $valid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
