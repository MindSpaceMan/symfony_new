<?php

namespace App\Controller;

use App\Model\User;
use App\Repository\UserRepository;
use App\Service\SmsCodeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SmsCodeController
{
    public function __construct(
        private SmsCodeService $smsCodeService,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/api/request-code', name: 'request_code', methods: ['POST'])]
    public function requestCode(Request $request): JsonResponse
    {
        $phoneNumber = $request->request->get('phone');
        if (!$phoneNumber) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Номер телефона не указан'
            ], 400);
        }

        // Пытаемся запросить/сгенерировать код
        try {
            $code = $this->smsCodeService->generateConfirmationCode($phoneNumber);
            return new JsonResponse([
                'status' => 'ok',
                'code' => $code->getCode()
            ]);
        } catch (\RuntimeException $e) {
            // Например, если сработала блокировка
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 429);
        }
    }

    #[Route('/api/verify-code', name: 'verify_code', methods: ['POST'])]
    public function verifyCode(Request $request): JsonResponse
    {
        $phoneNumber = $request->request->get('phone');
        $codeValue   = $request->request->get('code');

        if (!$phoneNumber || !$codeValue) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Номер телефона или код не указаны'
            ], 400);
        }

        // Проверяем код
        $isValidCode = $this->smsCodeService->verifyCode($phoneNumber, $codeValue);

        if (!$isValidCode) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Код неверный или истёк',
            ], 400);
        }

        // Если код верный, проверяем юзера
        $user = $this->userRepository->findByPhoneNumber($phoneNumber);
        if (!$user) {
            // Регистрируем
            $user = User::createFromPhoneNumber($phoneNumber);
            $this->userRepository->save($user);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Вы успешно зарегистрировались',
                'user_id' => $user->getId()
            ]);
        } else {
            // Авторизуем
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Вы успешно авторизовались',
                'user_id' => $user->getId()
            ]);
        }
    }
}
