<?php

namespace App\Repository;

use App\Model\ConfirmationCode;
use Doctrine\DBAL\Connection;

class ConfirmationCodeRepository
{
    private Connection $connection;
    private string $tableName = 'confirmation_codes';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Сохраняем новый код подтверждения в БД.
     * Возвращаем объект уже с ID (если используем автоинкремент).
     */
    public function save(ConfirmationCode $code): void
    {
        // Если ID ещё нет, делаем INSERT
        if ($code->getId() === null) {
            $sql = sprintf(
                'INSERT INTO %s (phone_number, code, created_at, is_used) VALUES (:phone_number, :code, :created_at, :is_used)',
                $this->tableName
            );

            // Выполняем запрос
            $this->connection->executeStatement($sql, [
                'phone_number' => $code->getPhoneNumber(),
                'code'         => $code->getCode(),
                'created_at'   => $code->getCreatedAt()->format('Y-m-d H:i:s'),
                'is_used'      => $code->isUsed() ? 1 : 0,
            ]);

            // Получим ID последней вставки ( PostgreSQL + DBAL - зависит от драйвера )
            $id = (int)$this->connection->lastInsertId($this->tableName . '_id_seq');
            // Или используем RETURNING id, если PostgreSQL
            // $id = (int)$this->connection->fetchOne('SELECT LASTVAL()');

            // Установим ID в объект (нам может понадобиться reflection или сеттер)
            // Удобнее, конечно, добавить сеттер setId() в ConfirmationCode, но это на твой вкус:
            // $code->setId($id);
        } else {
            // Иначе делаем UPDATE
            $sql = sprintf(
                'UPDATE %s SET is_used = :is_used WHERE id = :id',
                $this->tableName
            );

            $this->connection->executeStatement($sql, [
                'id'      => $code->getId(),
                'is_used' => $code->isUsed() ? 1 : 0,
            ]);
        }
    }

    /**
     * Ищем текущий код по номеру телефона.
     * Можно возвращать массив или готовый объект ConfirmationCode.
     */
    public function findLastCode(string $phoneNumber): ?ConfirmationCode
    {
        $sql = sprintf(
            'SELECT * FROM %s WHERE phone_number = :phone ORDER BY created_at DESC LIMIT 1',
            $this->tableName
        );

        $row = $this->connection->fetchAssociative($sql, ['phone' => $phoneNumber]);

        if (!$row) {
            return null;
        }

        return ConfirmationCode::fromArray($row);
    }

    /**
     * Сколько кодов отправили за последние X минут?
     */
    public function countRecentCodes(string $phoneNumber, int $minutes): int
    {
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s WHERE phone_number = :phone AND created_at > NOW() - INTERVAL \'%d MINUTE\'',
            $this->tableName,
            $minutes
        );

        $count = $this->connection->fetchOne($sql, ['phone' => $phoneNumber]);

        return (int) $count;
    }

    /**
     * Находим конкретный код по телефону и проверяем, совпадает ли.
     */
    public function findValidCode(string $phoneNumber, string $code): ?ConfirmationCode
    {
        // Допустим, код действует 5 минут, проверим created_at > NOW() - 5 MIN
        // Или без лимита, если не надо
        $sql = sprintf(
            'SELECT * FROM %s 
             WHERE phone_number = :phone 
             AND code = :code
             AND created_at > NOW() - INTERVAL \'5 MINUTE\'
             AND is_used = 0
             ORDER BY created_at DESC LIMIT 1',
            $this->tableName
        );

        $row = $this->connection->fetchAssociative($sql, [
            'phone' => $phoneNumber,
            'code'  => $code,
        ]);

        if (!$row) {
            return null;
        }

        return ConfirmationCode::fromArray($row);
    }
}