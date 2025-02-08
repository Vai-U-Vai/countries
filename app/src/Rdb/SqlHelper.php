<?php

namespace App\Rdb;

use PDO;
use PDOException;
use Exception;

class SqlHelper
{
    private string $host;
    private string $port;
    private string $dbName;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->dbName = $_ENV['DB_NAME'] ?? 'countries';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'root';

        $this->pingDb();
    }

    /**
     * Создает соединение с базой данных.
     *
     * @return PDO Объект PDO для работы с базой данных.
     *
     * @throws Exception Если не удалось установить соединение с базой данных.
     */
    public function openDbConnection(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            return new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    /**
     * Проверяет доступность базы данных путем создания подключения и его закрытия.
     *
     * @throws Exception Если не удалось установить соединение с базой данных.
     */
    private function pingDb(): void
    {
        try {
            $pdo = $this->openDbConnection();
            $pdo = null; // Закрываем соединение
        } catch (Exception $e) {
            throw new Exception("База данных недоступна: " . $e->getMessage());
        }
    }
}
