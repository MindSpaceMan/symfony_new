<?php
// init_db.php

require __DIR__ . '/vendor/autoload.php'; // подключаем автозагрузчик Composer

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$connectionParams = [
    'dbname'   => getenv('POSTGRES_DB') ?: 'ayder-computer',
    'user'     => getenv('POSTGRES_USER') ?: 'postgres',
    'password' => getenv('POSTGRES_PASSWORD') ?: 'postgres',
    'host'     => getenv('POSTGRES_HOST') ?: 'database',
    'driver'   => 'pdo_pgsql',
];

$conn = DriverManager::getConnection($connectionParams);

$sqlUsers = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    phone_number VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL
);
SQL;

$sqlCodes = <<<SQL
CREATE TABLE IF NOT EXISTS confirmation_codes (
    id SERIAL PRIMARY KEY,
    phone_number VARCHAR(255) NOT NULL,
    code VARCHAR(4) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    is_used BOOLEAN NOT NULL DEFAULT false,
    blocked_until TIMESTAMP NULL
);
SQL;

$sqlCodeNull = <<<SQL
ALTER TABLE confirmation_codes
    ALTER COLUMN code DROP NOT NULL;
SQL;


$sqlPendingPhones = <<<SQL
CREATE TABLE IF NOT EXISTS pending_phones (
    id SERIAL PRIMARY KEY,
    phone_number VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL
);
SQL;

$sqlUserPhones = <<<SQL
CREATE TABLE IF NOT EXISTS user_phones (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    phone_number VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    confirmed_at TIMESTAMP DEFAULT NULL
);
SQL;



$conn->executeStatement($sqlUsers);
$conn->executeStatement($sqlCodes);
$conn->executeStatement($sqlCodeNull);
$conn->executeStatement($sqlPendingPhones);
$conn->executeStatement($sqlUserPhones);

echo "Таблицы созданы (если их ещё не было).\n";
