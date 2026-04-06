<?php

class Database
{
    public static function getConnection(): PDO
    {
        static $pdo = null;
        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $host = defined('DB_HOST') ? (string) DB_HOST : (string) getenv('DB_HOST');
        $dbname = defined('DB_NAME') ? (string) DB_NAME : (string) getenv('DB_NAME');
        $user = defined('DB_USER') ? (string) DB_USER : (string) getenv('DB_USER');
        $pass = defined('DB_PASS') ? (string) DB_PASS : (string) getenv('DB_PASS');
        $charset = defined('DB_CHARSET') ? (string) DB_CHARSET : 'utf8mb4';

        if ($host === '' || $dbname === '' || $user === '') {
            throw new RuntimeException('Database configuration is incomplete (DB_HOST, DB_NAME, DB_USER).');
        }

        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=' . $charset;

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;
    }
}
