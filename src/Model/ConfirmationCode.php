<?php

namespace App\Model;

use DateTimeInterface;

class ConfirmationCode
{
    private ?int $id = null;
    private string $phoneNumber;
    private string $code;
    private \DateTimeInterface $createdAt;
    private bool $isUsed = false;

    private function __construct(string $phoneNumber, string $code, DateTimeInterface $createdAt)
    {
        $this->phoneNumber = $phoneNumber;
        $this->code = $code;
        $this->createdAt = $createdAt;
    }

    /**
     * Фабричный метод: удобный способ создать новый ConfirmationCode.
     */
    public static function create(string $phoneNumber, string $code): self
    {
        return new self($phoneNumber, $code, new \DateTimeImmutable());
    }

    /**
     * Фабричный метод для "восстановления" объекта из БД.
     */
    public static function fromArray(array $data): self
    {
        $instance = new self(
            $data['phone_number'],
            $data['code'],
            new \DateTimeImmutable($data['created_at'])
        );
        $instance->id = (int) $data['id'];
        $instance->isUsed = (bool) ($data['is_used'] ?? false);

        return $instance;
    }

    // Геттеры и сеттеры

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function markUsed(): void
    {
        $this->isUsed = true;
    }
}