<?php

namespace App\Model;

class User
{
    private ?int $id = null;
    private string $phoneNumber;
    private ?string $name = null;

    private function __construct(string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Фабричный метод для создания нового пользователя.
     */
    public static function createFromPhoneNumber(string $phoneNumber): self
    {
        return new self($phoneNumber);
    }

    /**
     * "Восстанавливающий" метод из БД.
     */
    public static function fromArray(array $data): self
    {
        $instance = new self($data['phone_number']);
        $instance->id = (int) $data['id'];
        $instance->name = $data['name'] ?? null;

        return $instance;
    }

    // Геттеры и (опционально) сеттеры
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}