<?php
// init_db.php

require __DIR__ . '/vendor/autoload.php'; // подключаем автозагрузчик Composer

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$connectionParams = [
    'dbname'   => getenv('POSTGRES_DB') ?: 'app',
    'user'     => getenv('POSTGRES_USER') ?: 'app',
    'password' => getenv('POSTGRES_PASSWORD') ?: '!ChangeMe!',
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
    is_used BOOLEAN NOT NULL DEFAULT false
);
SQL;

$conn->executeStatement($sqlUsers);
$conn->executeStatement($sqlCodes);

echo "Таблицы созданы (если их ещё не было).\n";
