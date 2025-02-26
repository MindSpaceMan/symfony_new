<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ValidTaxNumber;

final readonly class CalculatePriceRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int     $product,

        #[Assert\NotBlank]
        #[ValidTaxNumber]
        public string  $taxNumber,

        #[Assert\Type('string')]
        public ?string $couponCode = null
    ) {}

}
