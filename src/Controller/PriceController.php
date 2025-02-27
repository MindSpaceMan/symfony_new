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

class PriceController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepo,
        private readonly CouponRepository $couponRepo,
        private readonly PriceCalculator $priceCalculator,
    ) {}

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
