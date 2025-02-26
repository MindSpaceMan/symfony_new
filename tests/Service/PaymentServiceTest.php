<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Payment\PaymentProcessorInterface;
use App\Service\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    private PaymentProcessorInterface $processorMock;
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->processorMock = $this->createMock(PaymentProcessorInterface::class);
        $this->service = new PaymentService($this->processorMock);
    }
    public function testPaySuccess(): void
    {
        $this->processorMock
            ->method('pay')
            ->with($this->equalTo(99.99)) // ✅ Проверяем, что передаётся float
            ->willReturn(true);

        $result = $this->service->pay(99.99);

        $this->assertTrue($result);
    }

    public function testPayFailure(): void
    {
        $this->processorMock
            ->method('pay')
            ->willReturn(false);

        $result = $this->service->pay(100.0);

        $this->assertFalse($result);
    }

    public function testPayThrowsException(): void
    {
        $this->processorMock
            ->method('pay')
            ->willThrowException(new \Exception('Payment error'));

        $result = $this->service->pay(50.0);

        $this->assertFalse($result, 'Сервис должен вернуть false при исключении.');
    }
}
