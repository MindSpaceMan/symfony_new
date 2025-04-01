<?php

namespace App\Service;

use App\Model\ConfirmationCode;
use App\Repository\ConfirmationCodeRepository;
use App\Repository\PendingPhoneRepository;
use App\Repository\UserRepository;
use App\Model\User;

class SmsCodeService
{
    private const BLOCK_LIMIT = 3;          // 3 кода
    private const BLOCK_INTERVAL_MIN = 15;  // за последние 15 минут
    private const BLOCK_DURATION = 60;      // блокируем на 60 минут (пример)

    public function __construct(
        private ConfirmationCodeRepository $confirmationCodeRepo,
        private UserRepository $userRepository,
        private PendingPhoneRepository $pendingPhoneRepository,
    ) {
    }

    /**
     * Сгенерировать/вернуть код подтверждения.
     * Бросает \RuntimeException, если пользователь заблокирован.
     */
    public function generateConfirmationCode(string $phoneNumber): ConfirmationCode
    {
        // 1) Проверяем, не заблокирован ли уже пользователь
        $isBlocked = $this->confirmationCodeRepo->isBlocked($phoneNumber);
        if ($isBlocked) {
            throw new \RuntimeException('Вы заблокированы на 1 час, попробуйте позже');
        }


        // 2) Считаем, сколько кодов за последние X минут
        $recentCount = $this->confirmationCodeRepo->countRecentCodes($phoneNumber, self::BLOCK_INTERVAL_MIN);
        if ($recentCount >= self::BLOCK_LIMIT) {
            // Запоминаем время блокировки
            $this->confirmationCodeRepo->addBlock($phoneNumber, new \DateTimeImmutable('+1 hour'));
            throw new \RuntimeException('Превышено кол-во попыток. Блокировка на 1 час.');
        }

        // 2. Находим последний код
        $lastCode = $this->confirmationCodeRepo->findLastCode($phoneNumber);

        // Если код отправлялся меньше минуты назад, возвращаем старый
        if ($lastCode && !$this->isOlderThanOneMinute($lastCode)) {
            return $lastCode;
        }

        // 3. Генерируем новый код
        $newCodeValue = sprintf('%04d', random_int(0, 9999));
        $newCode      = ConfirmationCode::create($phoneNumber, $newCodeValue);

        // 4. Сохраняем
        $this->confirmationCodeRepo->save($newCode);

        return $newCode;
    }

    /**
     * Проверка кода
     * Возвращает true, если код найден, не просрочен и не использован.
     */
    public function verifyCode(string $phoneNumber, string $codeValue): bool
    {
        $code = $this->confirmationCodeRepo->findValidCode($phoneNumber, $codeValue);

        if (!$code) {
            return false;
        }

        // Ставим флаг "is_used", чтобы нельзя было использовать код многократно
        $code->markUsed();
        $this->confirmationCodeRepo->save($code);

        // 3. Проверяем, есть ли уже пользователь
        $user = $this->userRepository->findByPhoneNumber($phoneNumber);
        if (!$user) {
            // Нет — создаём
            $user = User::createFromPhoneNumber($phoneNumber);
            $this->userRepository->save($user);

            // 4. Удаляем запись из pending_phones
            $this->pendingPhoneRepository->removePhoneNumber($phoneNumber);
        }

        return true;
    }

    private function isOlderThanOneMinute(ConfirmationCode $code): bool
    {
        $oneMinuteAgo = (new \DateTimeImmutable())->modify('-1 minute');
        return $code->getCreatedAt() < $oneMinuteAgo;
    }
}
