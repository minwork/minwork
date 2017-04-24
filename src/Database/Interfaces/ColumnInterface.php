<?php
namespace Minwork\Database\Interfaces;

interface ColumnInterface
{

    const DEFAULT_ESCAPE_CHAR = '"';

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
     * @param $escaped bool
     *            If table name should be enclosed with quotation corresponding to database driver
     * @return string
     */
    public function getName($escaped = true): string;

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
     * Get column value PHP data type (one of the type constants)
     *
     * @return string
     */
    public function getInternalType(): string;

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
     * If column value can be set to NULL
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
     * Format value according to column specification
     * 
     * @param mixed $value            
     * @return mixed
     */
    public function format($value);
}