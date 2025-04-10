<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SmsCodeService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SmsCodeController extends AbstractController
{
    public function __construct(
        private SmsCodeService $smsCodeService,
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {}

    #[Route('/api/request-code', name: 'request_code', methods: ['POST'])]
    /**
     *
     * @OA\Post(
     *     path="/api/request-code",
     *     summary="Запрос кода подтверждения",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"phone"},
     *                 @OA\Property(property="phone", type="string", example="+1234567890")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Код успешно возвращён"),
     *     @OA\Response(response=429, description="Слишком много запросов (достигнут лимит)"),
     *     @OA\Response(response=400, description="Некорректные входные данные")
     * )
     */
    public function requestCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $violations = $this->validator->validate($data['phone'] ?? null, [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 10])
        ]);

        if (count($violations) > 0) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid phone number',
            ], 400);
        }

        try {
            $code = $this->smsCodeService->generateConfirmationCode($data['phone']);

            return new JsonResponse([
                'status' => 'ok',
                'code' => $code->getCode()
            ], 200);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 429);
        }
    }

    #[Route('/api/verify-code', name: 'verify_code', methods: ['POST'])]
    /**
     * @OA\Post(
     *     path="/api/verify-code",
     *     summary="Подтверждение кода",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"phone", "code"},
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="code", type="string", example="1234")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Авторизация успешна"),
     *     @OA\Response(response=201, description="Регистрация успешна"),
     *     @OA\Response(response=400, description="Код неверный или истёк")
     * )
     */
    public function verifyCode(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $phone = $data['phone'] ?? null;
        $code  = $data['code'] ?? null;

        // Сначала проверяем, существует ли пользователь
        $existingUser = $this->userRepository->findByPhoneNumber($phone);

        if (!$phone || !$code) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Phone or code is missing',
            ], 400);
        }

        if (!$this->smsCodeService->verifyCode($phone, $code)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid or expired code',
            ], 400);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => $existingUser ? 'Вы успешно авторизовались' : 'Вы успешно зарегистрировались',
            'user_id' => $existingUser?->getId(),
        ], $existingUser ? 201 : 200);
    }
}
