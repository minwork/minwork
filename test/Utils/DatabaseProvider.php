<?php
namespace Test\Utils;

use Minwork\Database\Exceptions\DatabaseException;
use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\MySql\Database as MySqlDatabase;
use Minwork\Database\Prototypes\AbstractDatabase;
use Minwork\Database\Sqlite\Database as SqliteDatabase;
use Minwork\Helper\Arr;
use PDOException;

class DatabaseProvider
{
    const TYPE_SQLITE = 'sqlite';
    const TYPE_MYSQL = 'mysql';

    const DEFAULT_TYPE = self::TYPE_SQLITE;

    const TYPES = [
        self::TYPE_MYSQL,
        self::TYPE_SQLITE
    ];

    private static $connections = [];
    private static $tables = [];

    public static function getConfig(): array
    {
        $defaultCharset = AbstractDatabase::DEFAULT_CHARSET;
        $defaultType = self::DEFAULT_TYPE;
        $env = getenv();

        $host = $env['DB_HOST'] ?? 'localhost';
        $dbname = $env['DB_NAME'] ?? 'test';
        $user = $env['DB_USER'] ?? 'root';
        $password = $env['DB_PASS'] ?? '';
        $charset = $env['DB_CHARSET'] ?? $defaultCharset;
        $type = $env['DB_TYPE'] ?? $defaultType;

        $config = compact('host', 'dbname', 'user', 'password', 'charset', 'type', 'defaultCharset', 'defaultType') + ['options' => Arr::filterByKeys($env, ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'])];

        return $config;
    }

    public static function getDatabaseType(?array $config = null, bool $verbose = true): string
    {
        $config = $config ?? self::getConfig();
        $type = $config['type'];

        if (!in_array($type, self::TYPES)) {
            if ($verbose) {
                echo "Unrecognised database type '{$config['type']}', using default '{$config['defaultType']}' instead.";
            }
            $type = $config['defaultType'];
        }

        return $type;
    }

    /**
     * @param string|null $type
     * @param array|null $config
     * @return DatabaseInterface
     * @throws DatabaseException
     */
    public static function getDatabase(?string $type = null, ?array $config = null): DatabaseInterface
    {
        $config = $config ?? self::getConfig();
        $type = $type ?? self::getDatabaseType($config);

        if (array_key_exists($type, self::$connections)) {
            return self::$connections[$type];
        }

        switch ($type) {
            case self::TYPE_MYSQL:
                try {
                    $connection = new MySqlDatabase($config['host'], $config['dbname'], $config['user'], $config['password'], $config['charset']);
                } catch (PDOException $e) {
                    echo <<<EOT
Database test: Cannot connect to MySQL server using default params.
Error({$e->getCode()}): {$e->getMessage()}

Try specifying connection parameters via environment variables.

Currently used:
DB_HOST = '{$config['host']}' (default: 'localhost')
DB_NAME = '{$config['dbname']}' (default: 'test')
DB_USER = '{$config['user']}' (default: 'root')
DB_PASS = '{$config['password']}' (default: '')
DB_CHARSET = '{$config['charset']}' (default: '{$config['defaultCharset']}')

EOT;
                    throw new DatabaseException('Error while initializing Mysql Database connection');
                }
                break;
            case self::TYPE_SQLITE:
                $connection = new SqliteDatabase(':memory:');
                break;

            default:
                throw new DatabaseException("Internal database type unrecognised for '{$type}'");
        }

        self::$connections[$type] = $connection;

        return self::$connections[$type];
    }

    /**
     * @param string $name
     * @param array|null $columns
     * @return TableInterface
     * @throws DatabaseException
     */
    public static function getTable(string $name, ?array $columns = null): TableInterface
    {
        if (!array_key_exists($name, self::$tables)) {
            $db = self::getDatabase();
            $table = self::getTableClass();
            self::$tables[$name] = new $table($db, $name, $columns);
        }

        return self::$tables[$name];
    }

    /**
     * @param string|null $type
     * @param array|null $config
     * @return string
     * @throws DatabaseException
     */
    public static function getTableClass(?string $type = null, ?array $config = null): string
    {
        $type = $type ?? self::getDatabaseType($config);

        switch ($type) {
            case self::TYPE_MYSQL:
                return '\Minwork\Database\Mysql\Table';
            case self::TYPE_SQLITE:
                return '\Minwork\Database\Sqlite\Table';
        }

        throw new DatabaseException("Internal table type unrecognised for '{$type}'");
    }

    /**
     * @param string|null $type
     * @param array|null $config
     * @return string
     * @throws DatabaseException
     */
    public static function getColumnClass(?string $type = null, ?array $config = null): string
    {
        $type = $type ?? self::getDatabaseType($config);

        switch ($type) {
            case self::TYPE_MYSQL:
                return '\Minwork\Database\Mysql\Column';
            case self::TYPE_SQLITE:
                return '\Minwork\Database\Sqlite\Column';
        }

        throw new DatabaseException("Internal column type unrecognised for '{$type}'");
    }

    /**
     * @param string $name
     * @param string $type
     * @param null $defaultValue
     * @param bool $nullable
     * @param bool $primaryKey
     * @param bool $autoIncrement
     * @return mixed
     * @throws DatabaseException
     */
    public static function createColumn(string $name, string $type, $defaultValue = null, bool $nullable = false, bool $primaryKey = false, bool $autoIncrement = false)
    {
        $class = self::getColumnClass();
        return $class($name, $type, $defaultValue, $nullable, $primaryKey, $autoIncrement);
    }
}