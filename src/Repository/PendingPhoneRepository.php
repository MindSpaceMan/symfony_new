<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class PendingPhoneRepository
{
    public function __construct(
        private Connection $connection,
        private string $tableName = 'pending_phones'
    ) {
    }

    public function addPhoneNumber(string $phoneNumber): void
    {
        $sql = sprintf(
            'INSERT INTO %s (phone_number, created_at)
             VALUES (:phone, NOW())',
            $this->tableName
        );
        $this->connection->executeStatement($sql, [
            'phone' => $phoneNumber,
        ]);
    }

    public function findPhoneNumber(string $phoneNumber): bool
    {
        $sql = sprintf(
            'SELECT 1 FROM %s WHERE phone_number = :phone LIMIT 1',
            $this->tableName
        );
        $row = $this->connection->fetchOne($sql, [
            'phone' => $phoneNumber
        ]);
        return (bool) $row;
    }

    public function removePhoneNumber(string $phoneNumber): void
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE phone_number = :phone',
            $this->tableName
        );
        $this->connection->executeStatement($sql, [
            'phone' => $phoneNumber
        ]);
    }
}
