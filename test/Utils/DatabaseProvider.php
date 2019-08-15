<?php
namespace Test\Utils;

use Minwork\Database\Object\AbstractDatabase;
use Minwork\Helper\Arr;

class DatabaseProvider
{
    const DEFAULT_DATABASE_DRIVER = 'pdo_mysql';

    public static function getConfig(): array
    {
        $defaultCharset = AbstractDatabase::DEFAULT_CHARSET;
        $defaultDriver = self::DEFAULT_DATABASE_DRIVER;
        $env = getenv();

        $host = $env['DB_HOST'] ?? 'localhost';
        $dbname = $env['DB_NAME'] ?? 'test';
        $user = $env['DB_USER'] ?? 'root';
        $password = $env['DB_PASS'] ?? '';
        $charset = $env['DB_CHARSET'] ?? $defaultCharset;
        $driver = $env['DB_DRIVER'] ?? $defaultDriver;

        $config = compact('host', 'dbname', 'user', 'password', 'charset', 'driver', 'defaultCharset', 'defaultDriver') + ['options' => Arr::filterByKeys($env, ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'])];

        return $config;
    }
}