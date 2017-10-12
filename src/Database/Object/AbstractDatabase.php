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
 * Abstract database interface implementation extending PDO
 *
 * @author Christopher Kalkhoff
 *        
 */
abstract class AbstractDatabase extends \PDO implements DatabaseInterface
{

    const DEFAULT_CHARSET = 'utf8';

    const DEFAULT_ESCAPE_CHAR = "'";

    /**
     * Database host address
     *
     * @var string
     */
    protected $host;

    /**
     * Database name
     *
     * @var string
     */
    protected $name;

    /**
     * Database charset
     *
     * @var string
     */
    protected $charset;

    /**
     * Database options used in init method
     *
     * @var array
     */
    protected $options;

    /**
     *
     * @param string $host            
     * @param string $name            
     * @param string $user            
     * @param string $password            
     * @param string $charset            
     * @param array $options
     *            Additional database options used in init method
     */
    public function __construct(string $host, string $name = '', string $user = '', string $password = '', string $charset = self::DEFAULT_CHARSET, array $options = []): void
    {
        $this->setHost($host)
            ->setName($name)
            ->setCharset($charset)
            ->setOptions($options)
            ->init($user, $password);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::getHost()
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::getCharset()
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::getOptions()
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \PDO::exec()
     */
    public function exec(string $statement)
    {
        return parent::exec($statement);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::query()
     */
    public function query(string $statement)
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

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::escape()
     */
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
        } elseif (! is_string($value)) {
            $value = strval($value);
        }
        return ($quoted = $this->quote($value)) === false ? self::DEFAULT_ESCAPE_CHAR . Formatter::removeQuotes(Formatter::cleanString($value)) . self::DEFAULT_ESCAPE_CHAR : $quoted;
    }

    /**
     * Initialize database setting all neccessary options and calling PDO constructor
     *
     * @param string $user            
     * @param string $password            
     */
    abstract protected function init(string $user, string $password): self;

    /**
     * Set database host
     *
     * @param string $host            
     * @return self
     */
    protected function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set database name
     *
     * @param string $name            
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set database charset
     *
     * @param string $charset            
     * @return self
     */
    protected function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Set database options used in init method
     *
     * @param array $options            
     * @param bool $merge
     *            If supplied options array should be merged with current or replace them
     * @return self
     */
    protected function setOptions(array $options, bool $merge = true): self
    {
        if (empty($this->options) || ! $merge) {
            $this->options = $options;
        } else {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }
}
