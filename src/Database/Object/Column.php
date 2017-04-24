<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Object;

use Minwork\Database\Object\Database;
use Minwork\Database\Interfaces\ColumnInterface;

/**
 * Column object for table
 *
 * @author Christopher Kalkhoff
 *        
 */
class Column implements ColumnInterface
{

    protected $name;

    protected $type;

    protected $internalType;

    protected $defaultValue;

    protected $nullable;

    protected $isPrimaryKey;

    protected $autoIncrement;

    /**
     *
     * @param string $name            
     * @param string $type            
     * @param mixed $defaultValue            
     * @param bool $nullable            
     * @param bool $primaryKey            
     */
    public function __construct(string $name, string $type, $defaultValue = null, bool $nullable = false, bool $primaryKey = false, bool $autoIncrement = false)
    {
        $this->setName($name)
            ->setType($type)
            ->setDefaultValue($defaultValue)
            ->setNullable($nullable)
            ->setPrimaryKey($primaryKey)
            ->setAutoIncrement($autoIncrement);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->getName(false);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getName()
     */
    public function getName($escaped = true): string
    {
        return $escaped ? static::DEFAULT_ESCAPE_CHAR . $this->name . static::DEFAULT_ESCAPE_CHAR : $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setName()
     */
    public function setName(string $name): ColumnInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getType()
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setType()
     */
    public function setType(string $type): ColumnInterface
    {
        $this->type = $type;
        
        if ($this->hasType('int')) {
            $this->internalType = self::TYPE_INTEGER;
        } elseif ($this->hasType('float') || $this->hasType('decimal') || $this->hasType('double')) {
            $this->internalType = self::TYPE_DOUBLE;
        } elseif ($this->hasType('bool')) {
            $this->internalType = self::TYPE_BOOLEAN;
        } else {
            $this->internalType = self::TYPE_STRING;
        }
        
        return $this;
    }

    /**
     * Check if column type contains string which determine its internal type
     * 
     * @param string $type            
     * @return bool
     */
    protected function hasType(string $type): bool
    {
        return stripos($this->getType(), $type) !== false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getNull()
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setNull()
     */
    public function setNullable(bool $nullable = true): ColumnInterface
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::isPrimaryKey()
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setPrimaryKey()
     */
    public function setPrimaryKey(bool $primaryKey = true): ColumnInterface
    {
        $this->isPrimaryKey = $primaryKey;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getDefaultValue()
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setDefaultValue()
     */
    public function setDefaultValue($value): ColumnInterface
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::isAutoIncrement()
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setAutoIncrement()
     */
    public function setAutoIncrement(bool $autoIncrement = true): ColumnInterface
    {
        $this->autoIncrement = $autoIncrement;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getInternalType()
     */
    public function getInternalType(): string
    {
        return $this->internalType;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::format()
     */
    public function format($value)
    {
        if ((is_null($value) || strcasecmp($value, 'null')) && $this->isNullable()) {
            return null;
        }
        
        settype($value, $this->internalType);
        return $value;
    }
}