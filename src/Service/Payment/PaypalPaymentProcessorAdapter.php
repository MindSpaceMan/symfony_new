<?php
declare(strict_types=1);

namespace App\Service\Payment;

use Psr\Log\LoggerInterface;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

final class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    public function __construct(
        private readonly PaypalPaymentProcessor $paypalProcessor,
        private readonly LoggerInterface $logger)
    {}

    /**
     * @param float $amount
     * @return bool true, если оплата успешно, false — если исключение
     */
    public function pay(float $amount): bool
    {
        $priceInCents = (int) $amount;

        try {
            $this->paypalProcessor->pay($priceInCents);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Payment failed: " . $e->getMessage());
            return false;
        }
    }
}
