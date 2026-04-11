<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) Config::get('database.driver', 'mysql');
        $host = (string) Config::get('database.host', '127.0.0.1');
        $port = (string) Config::get('database.port', '3306');
        $database = (string) Config::get('database.database', 'zis_app');
        $charset = (string) Config::get('database.charset', 'utf8mb4');
        $username = (string) Config::get('database.username', 'root');
        $password = (string) Config::get('database.password', '');

        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return self::$connection;
    }
}
