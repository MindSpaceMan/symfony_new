<?php
declare(strict_types=1);

namespace App\Payment;

use Psr\Log\LoggerInterface;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
final class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    private LoggerInterface $logger;

    public function __construct(private PaypalPaymentProcessor $paypalProcessor, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param float $amount
     * @return bool true, если оплата успешно, false — если исключение
     */
    public function pay(float $amount): bool
    {
        $priceInCents = (int) round($amount * 100);

        try {
            $this->paypalProcessor->pay($priceInCents);
            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            return false;
        }
    }
}
