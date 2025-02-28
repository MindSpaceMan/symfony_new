<?php
declare(strict_types=1);

namespace App\Service\Payment;

use Psr\Log\LoggerInterface;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

final class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(private readonly StripePaymentProcessor $stripeProcessor,
                                private readonly LoggerInterface $logger)
    {}

    /**
     * @param int $priceInCents
     * @return bool true, если оплата успешно, false — если исключение
     */
    public function pay(int $priceInCents): bool
    {
        try {
            $success = $this->stripeProcessor->processPayment($priceInCents);
            if (!$success) {
                $this->logger->error("Stripe Payment failed: Transaction declined.");
            }
            return $success;
        } catch (\Exception $e) {
            $this->logger->error("Payment failed: " . $e->getMessage());
            return false;
        }
    }
}
