<?php
namespace Example\ApiServer\App\Main\Utility;

use Minwork\Database\Object\Database;
use Minwork\Storage\Basic\Session;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;

class Factory
{

    const DATABASE = 'database';

    const USER_STORAGE = 'user';
    
    const SESSION_STORAGE = 'session'; 

    /**
     * List of class objects
     * 
     * @var array
     */
    private static $objectList = [];
    
    private static $table;

    /**
     * Get object of specified class
     * 
     * @param string $name
     *            Class name
     * @return mixed If objected exists on objects list then return it or null otherwise
     */
    protected static function getClassObject(string $name)
    {
        if (array_key_exists($name, self::$objectList)) {
            return self::$objectList[$name];
        }
        return null;
    }

    protected static function setClassObject(string $name, $object)
    {
        self::$objectList[$name] = $object;
        return self::$objectList[$name];
    }

    public static function getDatabase(): Database
    {
        try {
            $db = new Database(DB_DRIVER, DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
            self::$table = '\Minwork\Database\MySql\Table';
        } catch (\Exception $e) {
            $db = new Database(Database::DRIVER_SQLITE, ':memory:');
            self::$table = '\Minwork\Database\Sqlite\Table';
        }
        return self::getClassObject(self::DATABASE) ?? self::setClassObject(self::DATABASE, $db);
    }

    public static function getUserStorage(): DatabaseStorageInterface
    {
        if ($storage = self::getClassObject(self::USER_STORAGE)) {
            return $storage;
        } else {
            $db = self::getDatabase();
            return self::setClassObject(self::USER_STORAGE, new self::$table($db, self::USER_STORAGE));
        }
    }
}