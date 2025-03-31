<?php

namespace App\Repository;

use App\Model\ConfirmationCode;
use Doctrine\DBAL\Connection;

class ConfirmationCodeRepository
{
    public function __construct(
        private Connection $connection,
        private string $tableName = 'confirmation_codes'
    ) {
    }

    public function save(ConfirmationCode $code): void
    {
        if ($code->getId() === null) {
            // INSERT
            $sql = sprintf(
                'INSERT INTO %s (phone_number, code, created_at, is_used) 
                 VALUES (:phone, :code, :created_at, :is_used)
                 RETURNING id',
                $this->tableName
            );
            // Используем RETURNING id (особенность PostgreSQL)
            $id = $this->connection->fetchOne($sql, [
                'phone'      => $code->getPhoneNumber(),
                'code'       => $code->getCode(),
                'created_at' => $code->getCreatedAt()->format('Y-m-d H:i:s'),
                'is_used'    => $code->isUsed() ? 1 : 0,
            ]);
            $code->setId((int) $id);
        } else {
            // UPDATE
            $sql = sprintf(
                'UPDATE %s SET is_used = :is_used WHERE id = :id',
                $this->tableName
            );
            $this->connection->executeStatement($sql, [
                'is_used' => $code->isUsed() ? 1 : 0,
                'id'      => $code->getId(),
            ]);
        }
    }

    public function findLastCode(string $phoneNumber): ?ConfirmationCode
    {
        $sql = sprintf(
            'SELECT * FROM %s WHERE phone_number = :phone ORDER BY created_at DESC LIMIT 1',
            $this->tableName
        );

        $row = $this->connection->fetchAssociative($sql, [
            'phone' => $phoneNumber
        ]);

        return $row ? ConfirmationCode::fromArray($row) : null;
    }

    public function countRecentCodes(string $phoneNumber, int $minutes): int
    {
        $sql = sprintf(
            "SELECT COUNT(*) FROM %s 
             WHERE phone_number = :phone 
             AND created_at > (NOW() - INTERVAL '%d MINUTES')",
            $this->tableName,
            $minutes
        );

        $count = $this->connection->fetchOne($sql, [
            'phone' => $phoneNumber
        ]);

        return (int) $count;
    }

    /**
     * Проверяем код и, например, что он создан менее чем 5 минут назад, is_used = false
     */
    public function findValidCode(string $phoneNumber, string $codeValue): ?ConfirmationCode
    {
        $sql = sprintf(
            "SELECT * FROM %s 
             WHERE phone_number = :phone 
               AND code = :code
               AND created_at > (NOW() - INTERVAL '5 MINUTES')
               AND is_used = false
             ORDER BY created_at DESC LIMIT 1",
            $this->tableName
        );

        $row = $this->connection->fetchAssociative($sql, [
            'phone' => $phoneNumber,
            'code'  => $codeValue,
        ]);

        if (!$row) {
            return null;
        }

        return ConfirmationCode::fromArray($row);
    }
}
