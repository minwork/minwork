<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Object;

use Minwork\Helper\Formatter;
use Minwork\Database\Interfaces\DatabaseInterface;

/**
 * Database extending PDO
 *
 * @author Christopher Kalkhoff
 *        
 */
class Database extends \PDO implements DatabaseInterface
{

    const DRIVER_MYSQL = 'mysql';

    const DRIVER_SQLITE = 'sqlite';

    const DEFAULT_CHARSET = 'utf8';

    const DEFAULT_ESCAPE_CHAR = "'";

    protected $driver;

    protected $host;

    protected $name;

    protected $charset;

    protected $options;

    /**
     *
     * @param string $driver            
     * @param string $host            
     * @param string $name
     *            Database name (<i>dbname</i> param)
     * @param string $user            
     * @param string $password            
     * @param string $charset            
     * @param array $options            
     */
    public function __construct(string $driver, string $host = '', string $name = '', string $user = '', string $password = '', string $charset = self::DEFAULT_CHARSET, array $options = [])
    {
        $this->setDriver($driver)
            ->setHost($host)
            ->setName($name)
            ->setCharset($charset)
            ->setOptions($options)
            ->init($user, $password);
    }

    protected function init(string $user, string $password)
    {
        switch ($this->driver) {
            case self::DRIVER_MYSQL:
                $this->setOptions([
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->getCharset()}"
                ]);
                
                $dsn = self::DRIVER_MYSQL . ':';
                if ($this->getHost()) {
                    $dsn .= 'host=' . $this->getHost();
                }
                if ($this->getName()) {
                    $dsn .= ';dbname=' . $this->getName();
                }
                parent::__construct($dsn, $user, $password, $this->getOptions());
                
                $this->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
                $this->exec("SET SQL_MODE=ANSI_QUOTES;");
                break;
            
            case self::DRIVER_SQLITE:
                parent::__construct(self::DRIVER_SQLITE . ':' . $this->getHost(), $user, $password, $this->getOptions());
                break;
            
            default:
                // TODO Check this
                parent::__construct("{$this->getDriver()}:{$this->getHost()}:{$this->getName()}", $user, $password, $this->getOptions());
                break;
        }
        
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function escape($value): string
    {
        // Convert null to string null
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = strval($value);
            } else {
                throw new \InvalidArgumentException('Object passed to escape method must be convertable to string');
            }
        } elseif (is_int($value) || is_float($value) || is_double($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return intval($value);
        } elseif (!is_string($value)) {
            $value = strval($value);
        }
        return ($quoted = $this->quote($value)) === false ? self::DEFAULT_ESCAPE_CHAR . Formatter::removeQuotes(Formatter::cleanString($value)) . self::DEFAULT_ESCAPE_CHAR : $quoted;
    }

    public function setDriver(string $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function setOptions(array $options, bool $merge = true): self
    {
        if (empty($this->options) || ! $merge) {
            $this->options = $options;
        } else {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function exec($statement)
    {
        return parent::exec($statement);
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::query()
     */
    public function query($statement)
    {
        return parent::query($statement);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::getLastInsertId()
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId();
    }
}
