<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Payment\PaymentProcessorInterface;
use App\Service\Payment\PaymentService;
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
            ->with($this->equalTo(9999))
            ->willReturn(true);

        $result = $this->service->pay(9999);

        $this->assertTrue($result);
    }

    public function testPayFailure(): void
    {
        $this->processorMock
            ->method('pay')
            ->willReturn(false);

        $result = $this->service->pay(10000);

        $this->assertFalse($result);
    }

    public function testPayThrowsException(): void
    {
        $this->processorMock
            ->method('pay')
            ->willThrowException(new \Exception('Payment error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment error');

        $this->service->pay(5000);
    }
}
