<?php

namespace App\Repository;

use App\Model\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    private Connection $connection;
    private string $tableName = 'users';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            // INSERT
            $sql = sprintf(
                'INSERT INTO %s (phone_number, name) VALUES (:phone_number, :name)',
                $this->tableName
            );

            $this->connection->executeStatement($sql, [
                'phone_number' => $user->getPhoneNumber(),
                'name'         => $user->getName(),
            ]);

            // получаем id
            $id = (int)$this->connection->lastInsertId($this->tableName.'_id_seq');
            $user->setId($id); // <-- желательно добавить setId() в модель User
        } else {
            // UPDATE БЕЗ phone_number
            // то есть НЕ трогаем поле phone_number, предполагаем, что оно не меняется
            $sql = sprintf(
                'UPDATE %s SET name = :name WHERE id = :id',
                $this->tableName
            );

            $this->connection->executeStatement($sql, [
                'name' => $user->getName(),
                'id'   => $user->getId(),
            ]);
        }
    }


    public function findByPhoneNumber(string $phoneNumber): ?User
    {
        $sql = sprintf('SELECT * FROM %s WHERE phone_number = :phone LIMIT 1', $this->tableName);

        $row = $this->connection->fetchAssociative($sql, [
            'phone' => $phoneNumber,
        ]);

        if (!$row) {
            return null;
        }

        return User::fromArray($row);
    }
}
