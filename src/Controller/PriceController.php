<?php
declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\CouponService;
use App\Service\MoneyFormatter;
use App\Service\Payment\PaymentProcessorFactory;
use App\Service\PriceCalculator;
use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use OpenApi\Attributes as OA;

class PriceController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepo,
        private readonly CouponRepository $couponRepo,
        private readonly PriceCalculator $priceCalculator,
    ) {}

    #[OA\Post(
        path: '/calculate-price',
        summary: 'Рассчитывает цену продукта с учетом купона и налога',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product', 'taxNumber'],
                properties: [
                    new OA\Property(property: 'product', type: 'integer', example: 1),
                    new OA\Property(property: 'taxNumber', type: 'string', example: 'DE123456789'),
                    new OA\Property(property: 'couponCode', type: 'string', example: 'D15')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Цена успешно рассчитана'),
            new OA\Response(response: 422, description: 'Неверные входные данные')
        ]
    )]
    #[Route('/calculate-price', methods: ['POST'])]
    public function calculatePrice(
        #[MapRequestPayload] CalculatePriceRequest $dto,
        CouponService $couponService,
        MoneyFormatter $moneyFormatter
    ): JsonResponse
    {
        $product = $this->productRepo->find($dto->product);
        if (!$product) {
            return $this->json(['errors' => ['product' => 'Product not found']], 422);
        }

        $coupon = $couponService->getCoupon($dto->couponCode);
        $finalPrice = $this->priceCalculator->calculatePrice($product, $coupon, $dto->taxNumber);

        return $this->json([
                'finalPrice' => $moneyFormatter->format($finalPrice)
            ]
        );
    }

    /**
     * @throws RoundingNecessaryException
     * @throws DivisionByZeroException
     * @throws MathException
     * @throws NumberFormatException
     */
    #[OA\Post(
        path: '/purchase',
        summary: 'Осуществляет покупку продукта с проведением оплаты',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product', 'taxNumber', 'paymentProcessor'],
                properties: [
                    new OA\Property(property: 'product', type: 'integer', example: 1),
                    new OA\Property(property: 'taxNumber', type: 'string', example: 'IT12345678900'),
                    new OA\Property(property: 'couponCode', type: 'string', example: 'D15'),
                    new OA\Property(property: 'paymentProcessor', type: 'string', example: 'paypal')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Покупка успешно завершена'),
            new OA\Response(response: 422, description: 'Ошибка оплаты или неверные входные данные')
        ]
    )]
    #[Route('/purchase', methods: ['POST'])]
    public function purchase(
        #[MapRequestPayload] PurchaseRequest $dto,
        PaymentProcessorFactory $paymentProcessorFactory,
        CouponService $couponService,
        MoneyFormatter $moneyFormatter
    ): JsonResponse {

        $product = $this->productRepo->find($dto->product);
        if (!$product) {
            return $this->json(['errors' => ['product' => 'Product not found']], 422);
        }

        $coupon = $couponService->getCoupon($dto->couponCode);
        $finalPrice = $this->priceCalculator->calculatePrice($product, $coupon, $dto->taxNumber);

        $paymentService = $paymentProcessorFactory->getProcessor($dto->paymentProcessor);

        if (!$paymentService->pay($finalPrice->toInt())) {
            return $this->json(['errors' => ['payment' => 'Payment failed']], 422);
        }

        return $this->json([
            'status' => 'success',
            'finalPrice' => $moneyFormatter->format($finalPrice)
        ]);
    }
}
