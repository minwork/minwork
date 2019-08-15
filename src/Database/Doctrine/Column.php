<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Doctrine;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column as DocColumn;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Interfaces\DatabaseInterface;

class Column implements ColumnInterface
{
    /**
     * @var DocColumn
     */
    protected $column;

    /**
     * @var bool
     */
    protected $primaryKey = false;

    /**
     * @var string|null
     */
    protected $internalType = null;

    /**
     * Column constructor.
     * @param string $name
     * @param string $type
     * @param null $defaultValue
     * @param bool $nullable
     * @param bool $primaryKey
     * @param bool $autoIncrement
     * @throws DBALException
     */
    public function __construct(string $name, string $type, $defaultValue = null, bool $nullable = false, bool $primaryKey = false, bool $autoIncrement = false)
    {
        $this->column = new DocColumn($name, Type::getType($type));

        $this->setDefaultValue($defaultValue)->setNullable($nullable)->setPrimaryKey($primaryKey)->setAutoIncrement($autoIncrement);
    }

    public function getDoctrineColumn(): DocColumn
    {
        return $this->column;
    }

    public function getName(): string
    {
        return $this->column->getName();
    }

    public function getType(): string
    {
        return $this->column->getType()->getName();
    }

    /**
     * Check if column type contain supplied string.
     * This method is used to determine column internal type.
     *
     * @param string $type
     * @return bool
     */
    protected function hasType(string $type): bool
    {
        return stripos($this->getType(), $type) !== false;
    }

    /**
     * @param string $type
     * @return ColumnInterface
     * @throws DBALException
     */
    public function setType(string $type): ColumnInterface
    {
        $this->column->setType(Type::getType($type));
        $this->internalType = null;

        return $this;
    }

    public function setAutoIncrement(bool $autoIncrement = true): ColumnInterface
    {
        $this->column->setAutoincrement($autoIncrement);

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @param string $name
     * @return ColumnInterface
     * @throws DBALException
     */
    public function setName(string $name): ColumnInterface
    {
        $options = $this->column->toArray();
        $this->column = new DocColumn($name, Type::getType($this->getType()), $options);

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->column->getDefault();
    }

    public function setDefaultValue($value): ColumnInterface
    {
        $this->column->setDefault($value);

        return $this;
    }

    public function isNullable(): bool
    {
        return !$this->column->getNotnull();
    }

    public function setNullable(bool $nullable = true): ColumnInterface
    {
        $this->column->setNotnull(!$nullable);

        return $this;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey(bool $primaryKey = true): ColumnInterface
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function isAutoIncrement(): bool
    {
        return $this->column->getAutoincrement();
    }

    /**
     * @param mixed $value
     * @param Database|DatabaseInterface $database
     * @return mixed
     * @throws DBALException|InvalidArgumentException
     */
    public function format($value, DatabaseInterface $database)
    {
        if (!$database instanceof Database) {
            throw new InvalidArgumentException('Doctrine column must use Doctrine database to properly format value');
        }
        return $this->column->getType()->convertToPHPValue($value, $database->getConnection()->getDatabasePlatform());
    }
}