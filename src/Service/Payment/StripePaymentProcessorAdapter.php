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
     * @param float $amount
     * @return bool true, если оплата успешно, false — если исключение
     */
    public function pay(float $amount): bool
    {
        $priceInCents = (int) round($amount * 100);

        try {
            $success = $this->stripeProcessor->processPayment($amount);
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
