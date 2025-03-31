<?php

namespace App\Model;

class ConfirmationCode
{
    private ?int $id = null;
    private string $phoneNumber;
    private string $code;
    private \DateTimeInterface $createdAt;
    private bool $isUsed = false;

    private function __construct(string $phoneNumber, string $code, \DateTimeInterface $createdAt)
    {
        $this->phoneNumber = $phoneNumber;
        $this->code        = $code;
        $this->createdAt   = $createdAt;
    }

    public static function create(string $phoneNumber, string $code): self
    {
        return new self($phoneNumber, $code, new \DateTimeImmutable());
    }

    public static function fromArray(array $data): self
    {
        $instance = new self(
            $data['phone_number'],
            $data['code'],
            new \DateTimeImmutable($data['created_at'])
        );
        $instance->id     = (int) $data['id'];
        $instance->isUsed = (bool) ($data['is_used'] ?? false);

        return $instance;
    }

    // Геттеры / Сеттеры
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCreatedAt(): \DateTimeInterface
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
