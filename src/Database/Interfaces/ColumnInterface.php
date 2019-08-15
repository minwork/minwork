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

    const TYPE_DOUBLE = 'double';

    const TYPE_BOOLEAN = 'bool';

    const TYPE_STRING = 'string';

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
     * Format value according to mapped PHP type depending on database platform
     *
     * @param mixed $value
     * @param DatabaseInterface $database
     * @return mixed
     */
    public function format($value, DatabaseInterface $database);
}