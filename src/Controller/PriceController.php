<?php

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\PaymentService;
use App\Service\PriceCalculator;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepo,
        private CouponRepository $couponRepo,
        private PriceCalculator $priceCalculator
    ) {}

    /**
     * @OA\Post(
     *   summary="Рассчитать итоговую цену",
     *   description="Принимает данные о продукте, налоговом номере и купоне. Возвращает финальную цену.",
     *   @OA\RequestBody(
     *       description="Данные для расчёта цены",
     *       required=true,
     *       @OA\JsonContent(ref=@Model(type=CalculatePriceRequest::class))
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Успешный расчёт",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="finalPrice", type="number", format="float")
     *       )
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="В случае ошибки валидации или неверных данных",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="errors", type="object")
     *       )
     *   )
     * )
     *
     * @OA\Tag(name="Price")
     */
    public function calculatePrice(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new CalculatePriceRequest();
        $dto->setProduct($data['product'] ?? null);
        $dto->setTaxNumber($data['taxNumber'] ?? null);
        $dto->setCouponCode($data['couponCode'] ?? null);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => $this->formatErrors($errors)], 400);
        }

        $product = $this->productRepo->find($dto->getProduct());
        if (!$product) {
            return $this->json(['errors' => ['product' => 'Product not found']], 400);
        }

        $coupon = null;
        if ($dto->getCouponCode()) {
            $coupon = $this->couponRepo->findOneBy(['code' => $dto->getCouponCode()]);
            if (!$coupon) {
                return $this->json(['errors' => ['couponCode' => 'Coupon not found']], 400);
            }
        }

        $finalPrice = $this->priceCalculator->calculatePrice($product, $coupon, $dto->getTaxNumber());
        return $this->json(['finalPrice' => $finalPrice], 200);
    }

    /**
     * @OA\Post(
     *   summary="Совершить покупку",
     *   description="Принимает данные о продукте, налоговом номере, купоне, способе оплаты. Возвращает итоговую цену и статус оплаты.",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(ref=@Model(type=PurchaseRequest::class))
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Покупка прошла успешно",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="status", type="string"),
     *           @OA\Property(property="finalPrice", type="number", format="float")
     *       )
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Оплата не прошла или ошибка входных данных",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="errors", type="object")
     *       )
     *   )
     * )
     *
     * @OA\Tag(name="Price")
     */
    public function purchase(
        Request $request,
        ValidatorInterface $validator,
        PaymentService $paymentServicePaypal,
        PaymentService $paymentServiceStripe
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new PurchaseRequest();
        $dto->setProduct($data['product'] ?? null);
        $dto->setTaxNumber($data['taxNumber'] ?? null);
        $dto->setCouponCode($data['couponCode'] ?? null);
        $dto->setPaymentProcessor($data['paymentProcessor'] ?? null);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => $this->formatErrors($errors)], 400);
        }

        $product = $this->productRepo->find($dto->getProduct());
        if (!$product) {
            return $this->json(['errors' => ['product' => 'Product not found']], 400);
        }

        $coupon = null;
        if ($dto->getCouponCode()) {
            $coupon = $this->couponRepo->findOneBy(['code' => $dto->getCouponCode()]);
            if (!$coupon) {
                return $this->json(['errors' => ['couponCode' => 'Coupon not found']], 400);
            }
        }

        $finalPrice = $this->priceCalculator->calculatePrice($product, $coupon, $dto->getTaxNumber());

        $paymentService = match ($dto->getPaymentProcessor()) {
            'paypal' => $paymentServicePaypal,
            'stripe' => $paymentServiceStripe,
            default => null,
        };

        if (!$paymentService) {
            return $this->json(['errors' => ['paymentProcessor' => 'Unknown payment processor']], 400);
        }

        $success = $paymentService->pay($finalPrice);
        if (!$success) {
            return $this->json(['errors' => ['payment' => 'Payment failed']], 400);
        }

        return $this->json([
            'status' => 'success',
            'finalPrice' => $finalPrice
        ], 200);
    }

    private function formatErrors(iterable $errors): array
    {
        $res = [];
        foreach ($errors as $error) {
            $res[$error->getPropertyPath()] = $error->getMessage();
        }
        return $res;
    }
}
