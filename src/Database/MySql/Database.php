<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\MySql;

use Minwork\Database\Object\AbstractDatabase;

/**
 * MySQL implementation of database
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
        $this->setOptions([
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->getCharset()}"
        ]);
        
        $dsn = 'mysql:';
        if ($this->getHost()) {
            $dsn .= 'host=' . $this->getHost();
        }
        if ($this->getName()) {
            $dsn .= ';dbname=' . $this->getName();
        }
        \PDO::__construct($dsn, $user, $password, $this->getOptions());
        
        $this->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        $this->exec("SET SQL_MODE=ANSI_QUOTES;");
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
}