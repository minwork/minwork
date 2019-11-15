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
use PDO;

/**
 * SQLite implementation of database
 *
 * @author Christopher Kalkhoff
 *        
 */
class Database extends AbstractDatabase
{
    protected function createDsn(): string
    {
        return "sqlite:{$this->getHost()}";
    }
}