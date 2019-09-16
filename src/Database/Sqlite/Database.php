<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Sqlite;

use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Prototypes\AbstractDatabase;

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
     * @see \Minwork\Database\Prototypes\AbstractDatabase::init()
     */
    protected function init(string $user, string $password): DatabaseInterface
    {
        \PDO::__construct("sqlite:{$this->getHost()}", $user, $password, $this->getOptions());
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $this;
    }
}