<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Interfaces;

/**
 * Interface for table column
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ColumnInterface
{

    const TYPE_INTEGER = 'int';

    const TYPE_FLOAT = 'float';

    const TYPE_BOOLEAN = 'bool';

    const TYPE_STRING = 'string';

    const TYPE_TEXT = 'text';

    const TYPE_DATETIME = 'datetime';

    const TYPE_NULL = 'null';

    const TYPES_LIST = [
        self::TYPE_INTEGER,
        self::TYPE_FLOAT,
        self::TYPE_BOOLEAN,
        self::TYPE_DATETIME,
        self::TYPE_STRING,
        self::TYPE_TEXT,
        self::TYPE_NULL,
    ];

    /**
     * Return string representation of column (typically it's unescaped name)
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get column name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set column name
     *
     * @param string $name            
     * @return self
     */
    public function setName(string $name): self;

    /**
     * Get column type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set column type
     *
     * @param string $type            
     * @return self
     */
    public function setType(string $type): self;

    /**
     * Get column default value
     *
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * Set column default value
     *
     * @param mixed $value            
     * @return self
     */
    public function setDefaultValue($value): self;

    /**
     * If column can have NULL as value
     *
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Set if column value can be set to NULL
     *
     * @param bool $nullable            
     * @return self
     */
    public function setNullable(bool $nullable = true): self;

    /**
     * If column is primary key
     *
     * @return bool
     */
    public function isPrimaryKey(): bool;

    /**
     * Set if column is a primary key
     *
     * @param bool $primaryKey            
     * @return self
     */
    public function setPrimaryKey(bool $primaryKey = true): self;

    /**
     * If column should be automatically incremented (works only for primary key)
     *
     * @return bool
     */
    public function isAutoIncrement(): bool;

    /**
     * Set if column should be automatically incremented (works only for primary key)
     *
     * @param bool $autoIncrement            
     * @return self
     */
    public function setAutoIncrement(bool $autoIncrement = true): self;

    /**
     * Get column length (mostly used for string and numeric types)
     *
     * @return int|string|null
     */
    public function getLength();

    /**
     * Set column length (can be null for column that doesn't require specifying length)
     *
     * @param int|string|null $length
     * @return ColumnInterface
     */
    public function setLength($length): self;

    /**
     * Get custom column properties which can be used for column creation or type mapping
     *
     * @return array
     */
    public function getProperties(): array;

    /**
     * Set custom column properties specific for column driver
     *
     * @param array $properties
     * @return ColumnInterface
     */
    public function setProperties(array $properties): self;

    /**
     * Get driver specific column type definition like for example: VARCHAR(255) or BOOLEAN.<br>
     * This value should be automatically generated if not manually set by setDatabaseType method
     *
     * @see ColumnInterface::setDatabaseType()
     * @return string
     */
    public function getDatabaseType();

    /**
     * Explicitly set column database type, used for column creation or altering
     *
     * @see ColumnInterface::getDatabaseType()
     * @param $type
     * @return ColumnInterface
     */
    public function setDatabaseType($type): ColumnInterface;

    /**
     * Format value according to column type specified by setType or in constructor
     * @see ColumnInterface::TYPES_LIST
     *
     * @param mixed $value
     * @param array|null $mapping Optional mapping of ColumnInterface type to php type (see settype function) or custom callback which transforms value
     * @return mixed
     */
    public function format($value, ?array $mapping = null);
}