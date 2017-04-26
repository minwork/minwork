<?php
namespace Minwork\Database\Sqlite;

use Minwork\Database\Object\AbstractDatabase;

/**
 * SQLite implementation of database
 *
 * @author Christopher Kalkhoff
 *        
 */
class Database extends AbstractDatabase
{

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Object\AbstractDatabase::init()
     */
    protected function init(string $user, string $password)
    {
        \PDO::__construct("sqlite:{$this->getHost()}", $user, $password, $this->getOptions());
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
}