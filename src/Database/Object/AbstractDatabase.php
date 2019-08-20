<?php
/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpMissingParentConstructorInspection */

/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Object;

use InvalidArgumentException;
use Minwork\Database\Exceptions\DatabaseException;
use Minwork\Helper\Formatter;
use Minwork\Database\Interfaces\DatabaseInterface;
use PDO;

/**
 * Abstract database interface implementation extending PDO
 *
 * @author Christopher Kalkhoff
 *        
 */
abstract class AbstractDatabase extends PDO implements DatabaseInterface
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
     * Transactions counter for supporting nested transactions
     * @var int
     */
    protected $transactions = 0;

    /**
     * If transactions can only roll back cause one of nested transactions failed
     * @var bool
     */
    protected $isRollbackOnly = false;

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
    public function __construct(string $host, ?string $name = null, ?string $user = null, ?string $password = null, ?string $charset = null, array $options = [])
    {
        $this->setHost($host)
            ->setName($name ?? '')
            ->setCharset($charset ?? self::DEFAULT_CHARSET)
            ->setOptions($options)
            ->init($user ?? '', $password ?? '');
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

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\DatabaseInterface::escape()
     */
    public function escape($value, $type = null): string
    {
        // Convert null to string null
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = strval($value);
            } else {
                throw new InvalidArgumentException('Object passed to escape method must be convertable to string');
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
     * Initialize database by setting all neccessary options and calling PDO constructor
     *
     * @param string $user
     * @param string $password
     * @return DatabaseInterface
     */
    abstract protected function init(string $user, string $password): DatabaseInterface;

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

    public function beginTransaction()
    {
        // If already in transaction then increment transactions counter to silently support nested transactions
        if (!$this->inTransaction()) {
            parent::beginTransaction();
        }
        ++$this->transactions;
    }

    /**
     * @return void
     * @throws DatabaseException
     */
    public function commit(): void
    {
        if ($this->isRollbackOnly) {
            throw DatabaseException::transactionRollbackOnly();
        }
        // If have nested transactions then just decrement counter instead of actually committing
        if (--$this->transactions <= 0) {
            parent::commit();
        }
    }

    /**
     * @return bool|mixed
     * @throws DatabaseException
     */
    public function rollBack(): void
    {
        if (!$this->inTransaction()) {
            throw DatabaseException::noTransaction();
        }

        if (--$this->transactions <= 0) {
            $this->isRollbackOnly = false;
            parent::rollBack();
        } else {
            $this->isRollbackOnly = true;
        }
    }
}
