<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\MySql;

use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Prototypes\AbstractDatabase;
use PDO;

/**
 * MySQL implementation of database
 *
 * @author Christopher Kalkhoff
 *        
 */
class Database extends AbstractDatabase
{

    protected function createDsn(): string
    {
        $dsn = 'mysql:';
        if ($this->getHost()) {
            $dsn .= 'host=' . $this->getHost();

            if ($this->getName()) {
                $dsn .= ';dbname=' . $this->getName();
            }
        }

        return $dsn;
    }

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->getCharset()}",
            PDO::ATTR_AUTOCOMMIT => 1,
        ]);
    }

    protected function initDatabase(): void
    {
        //$this->exec("SET SQL_MODE=ANSI_QUOTES;");
    }
}