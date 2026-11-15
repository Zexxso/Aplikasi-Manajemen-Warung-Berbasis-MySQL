<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Default configuration matching existing Laravel .env
            $host = '127.0.0.1';
            $port = '3306';
            $dbName = 'warungku';
            $username = 'root';
            $password = '';
            $charset = 'utf8mb4';

            // Check if .env in Laravel exists to read credentials dynamically
            $envPath = dirname(__DIR__, 2) . '/warungku/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, '#')) continue;
                    if (str_contains($line, '=')) {
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v, " \t\n\r\0\x0B\"'");
                        if ($k === 'DB_HOST') $host = $v;
                        if ($k === 'DB_PORT') $port = $v;
                        if ($k === 'DB_DATABASE') $dbName = $v;
                        if ($k === 'DB_USERNAME') $username = $v;
                        if ($k === 'DB_PASSWORD') $password = $v;
                    }
                }
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}

function db(): PDO
{
    return Database::getConnection();
}

