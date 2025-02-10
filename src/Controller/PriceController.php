<?php

// src/Controller/PriceController.php
namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use App\Service\PriceCalculator;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepo,
        private CouponRepository $couponRepo,
        private PriceCalculator $priceCalculator
    ) {}

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
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

        // Проверка продукта
        $product = $this->productRepo->find($dto->getProduct());
        if (!$product) {
            return $this->json(['errors' => ['product' => 'Product not found']], 400);
        }

        // Проверка купона
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

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
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

        return $this->json(['status' => 'success', 'finalPrice' => $finalPrice], 200);
    }

    private function formatErrors($errors): array
    {
        $res = [];
        foreach ($errors as $error) {
            $res[$error->getPropertyPath()] = $error->getMessage();
        }
        return $res;
    }
}
