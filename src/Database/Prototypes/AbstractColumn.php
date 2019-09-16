<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Prototypes;

use Minwork\Database\Exceptions\DatabaseException;
use Minwork\Database\Interfaces\ColumnInterface;

/**
 * Column object used by table for creation, synchronization with database or formatting its value using appropiate PHP type
 *
 * @author Christopher Kalkhoff
 *        
 */
abstract class AbstractColumn implements ColumnInterface
{

    /**
     * Unescaped column name
     *
     * @var string
     */
    protected $name;

    /**
     * One of predefined ColumnInterface types which helps map column value to proper PHP type
     *
     * @see ColumnInterface::TYPES_LIST
     * @var string
     */
    protected $type;

    /**
     * Column type defined according to specific driver like: INT or VARCHAR(255)
     * If this value is null when calling getDatabaseType method then database type should be automatically deducted
     *
     * @see AbstractColumn::getDatabaseType()
     * @var null|string|mixed
     */
    protected $databaseType = null;

    /**
     * Column default value
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * If column value can be NULL
     *
     * @var bool
     */
    protected $nullable;

    /**
     * If column is primary key
     *
     * @var bool
     */
    protected $isPrimaryKey;

    /**
     * If column should have automatically generated incremental value
     *
     * @var bool
     */
    protected $autoIncrement;

    /**
     * Column length used for some database types
     *
     * @var null|int
     */
    protected $length = null;

    /**
     * Various column properties specific for driver
     *
     * @var array
     */
    protected $properties = [];

    /**
     *
     * @param string $name            
     * @param string $type            
     * @param mixed $defaultValue            
     * @param bool $nullable            
     * @param bool $primaryKey            
     * @param bool $autoIncrement            
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
        return $this->getName();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
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
     * @throws DatabaseException
     * @see \Minwork\Database\Interfaces\ColumnInterface::setType()
     */
    public function setType(string $type): ColumnInterface
    {
        // If column type is not recognised then throw appropriate exception
        if (!in_array($type, ColumnInterface::TYPES_LIST)) {
            throw DatabaseException::invalidColumnType($type);
        }
        $this->type = $type;
        
        return $this;
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
     * @see \Minwork\Database\Interfaces\ColumnInterface::getLength()
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setLength()
     */
    public function setLength($length): ColumnInterface
    {
        $this->length = $length;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::getProperties()
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\ColumnInterface::setProperties()
     */
    public function setProperties(array $properties): ColumnInterface
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Create new column object based on driver specific column definition
     * @param array|mixed $definition
     * @return mixed
     */
    abstract public static function createFromDefinition($definition): ColumnInterface;

    /**
     * Utility method for naive types mapping
     * @param $type
     * @param $typeString
     * @return bool
     */
    protected static function hasType($type, $typeString): bool
    {
        return stripos($type, $typeString) !== false;
    }

    /**
     * Naive database type mapping
     *
     * @param $dbType
     * @return string
     */
    protected static function mapType($dbType): string
    {
        if (self::hasType($dbType, 'int')) {
            return self::TYPE_INTEGER;
        } elseif (self::hasType($dbType, 'float') || self::hasType($dbType, 'decimal') || self::hasType($dbType, 'double')) {
            return self::TYPE_FLOAT;
        } elseif (self::hasType($dbType, 'bool')) {
            return self::TYPE_BOOLEAN;
        } elseif (self::hasType($dbType, 'date') || self::hasType($dbType, 'time')) {
            return self::TYPE_DATETIME;
        }

        return self::TYPE_STRING;
    }

    public function setDatabaseType($type): ColumnInterface
    {
        $this->databaseType = $type;

        return $this;
    }


    /**
     *
     * {@inheritdoc}
     *
     * @throws DatabaseException
     * @see \Minwork\Database\Interfaces\ColumnInterface::format()
     */
    public function format($value, ?array $mapping = null)
    {
        if ((is_null($value) || strcasecmp($value, 'null') === 0) && $this->isNullable()) {
            $columnType = ColumnInterface::TYPE_NULL;
        } else {
            $columnType = $this->getType();
        }

        if (is_array($mapping) && array_key_exists($columnType, $mapping)) {
            $mappedType = $mapping[$columnType];
            if (is_string($mappedType)) {
                settype($value, $mappedType);
                return $value;
            } elseif (is_callable($mappedType)) {
                return $mappedType($value);
            }
            // If neither string nor callable then proceed with default mapping
        }

        switch ($columnType) {
            case ColumnInterface::TYPE_STRING:
            case ColumnInterface::TYPE_TEXT:
            case ColumnInterface::TYPE_DATETIME:
                return strval($value);

            case ColumnInterface::TYPE_INTEGER:
                return intval($value);

            case ColumnInterface::TYPE_FLOAT:
                return floatval($value);

            case ColumnInterface::TYPE_BOOLEAN:
                return boolval($value);

            case ColumnInterface::TYPE_NULL:
                return null;
        }

        return $value;
    }
}