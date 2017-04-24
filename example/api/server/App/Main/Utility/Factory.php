<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Example\ApiServer\App\Main\Utility;

use Minwork\Database\Object\Database;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Database\MySql\Table as MySqlTable;
use Minwork\Database\Sqlite\Table as SqliteTable;

/**
 * Provide storage restricting the instantiation of a class to one object
 * 
 * @author Christopher Kalkhoff
 *        
 */
class Factory
{

    const USER_STORAGE = 'user';

    /**
     * List of storage objects
     *
     * @var array
     */
    private static $objectList = [];

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

    /**
     * Save object instance to internal array with $name as key then return it
     * 
     * @param string $name            
     * @param object $object            
     * @return mixed
     */
    protected static function setClassObject(string $name, $object)
    {
        self::$objectList[$name] = $object;
        return self::$objectList[$name];
    }

    /**
     * Return storage for user model
     * 
     * @return DatabaseStorageInterface
     */
    public static function getUserStorage(): DatabaseStorageInterface
    {
        if ($storage = self::getClassObject(self::USER_STORAGE)) {
            return $storage;
        } else {
            try {
                $db = new Database(DB_DRIVER, DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD);
                return self::setClassObject(self::USER_STORAGE, new MySqlTable($db, self::USER_STORAGE));
            } catch (\Exception $e) {
                $db = new Database(Database::DRIVER_SQLITE, ':memory:');
                return self::setClassObject(self::USER_STORAGE, new SqliteTable($db, self::USER_STORAGE));
            }
        }
    }
}