<?php

namespace App\Database;

use PDO;


class Database {
    private $readConnection;
    private $writeConnection;

    public function __construct() {
        $this->readConnection = $this->createConnection(
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_USER_READ'),
            getenv('DB_PASSWORD_READ')
        );

        $this->writeConnection = $this->createConnection(
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_USER_WRITE'),
            getenv('DB_PASSWORD_WRITE')
        );
    }

    private function createConnection($host, $dbName, $user, $password) {
        $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return $pdo;
    }

    public function getReadConnection() {
        return $this->readConnection;
    }

    public function getWriteConnection() {
        return $this->writeConnection;
    }
}
