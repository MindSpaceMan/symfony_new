<?php

namespace App\Tests\Service;

use App\Payment\PaymentProcessorInterface;
use App\Service\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    public function testPaySuccess(): void
    {
        // Создаем mock для PaymentProcessorInterface
        $processorMock = $this->createMock(PaymentProcessorInterface::class);

        // Настраиваем mock так, чтобы метод pay(float $amount) возвращал true
        $processorMock
            ->method('pay')
            ->with($this->equalTo(99.99))
            ->willReturn(true);

        // Создаем PaymentService с этим mock'ом
        $service = new PaymentService($processorMock);

        // Вызываем pay
        $result = $service->pay(99.99);

        // Проверяем, что метод вернул true
        $this->assertTrue($result);
    }

    public function testPayFailure(): void
    {
        $processorMock = $this->createMock(PaymentProcessorInterface::class);

        // На этот раз метод pay вернёт false
        $processorMock
            ->method('pay')
            ->willReturn(false);

        $service = new PaymentService($processorMock);
        $result = $service->pay(100.0);

        $this->assertFalse($result);
    }
}
