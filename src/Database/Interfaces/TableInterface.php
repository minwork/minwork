<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Interfaces;

use Minwork\Database\Utility\Condition;

/**
 * Interface for database table
 *
 * @author Christopher Kalkhoff
 *        
 */
interface TableInterface
{

    const DEFAULT_PK_FIELD = 'id';

    const DEFAULT_ESCAPE_CHAR = '"';

    const COLUMNS_ALL = '*';

    /**
     * ***************** Table operations *******************
     */
    
    /**
     * Create table based on columns list
     *
     * @param bool $replace
     *            If table should replace existing one (if applicable)
     * @return bool If table was successfully created
     */
    public function create(bool $replace = false): bool;

    /**
     * Remove all rows from table
     *
     * @return int Number of deleted rows
     */
    public function clear(): int;

    /**
     * Remove table from database
     *
     * @return bool If table was successfully removed
     */
    public function remove(): bool;

    /**
     * Synchronize database table with current table columns list
     *
     * @return bool If synchronization was successfull
     */
    public function synchronize(): bool;

    /**
     * ***************** Table queries *******************
     */
    
    /**
     * Select rows from table
     *
     * @see \Minwork\Database\Object\AbstractTable::prepareColumnsList()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @see \Minwork\Database\Object\AbstractTable::getOrderQuery()
     * @see \Minwork\Database\Object\AbstractTable::getLimitQuery()
     * @see \Minwork\Database\Object\AbstractTable::getGroupQuery()
     * @param array|string|Condition $conditions            
     * @param string|array $columns            
     * @param string|array|null $order            
     * @param string|array|int|null $limit            
     * @param string|array|null $group            
     * @return mixed Select result specific to database query/exec method implementation
     */
    public function select($conditions = [], $columns = self::COLUMNS_ALL, $limit = null, $order = null, $group = null);

    /**
     * Insert into table<br>
     * Values can have form of list or associative array with column names as keys like
     * <pre>
     * [true, 'value2', 3, null, ...]
     * ['id' => 5, 'new' => true, 'data' => 'test', 'changed_date' => null, ...]
     * </pre>
     *
     * @param array $values            
     * @return mixed
     */
    public function insert(array $values);

    /**
     * Update row(s) in table
     *
     * @param array $values
     *            Array of values only or column name as a key and corresponding value
     * @param array|string|Condition $conditions            
     * @param int|array|null $limit            
     * @return mixed
     */
    public function update(array $values, $conditions = [], $limit = null);

    /**
     * Delete row(s) from table
     *
     * @param array|string|Condition $conditions            
     * @param int|array|string $limit            
     * @return mixed
     */
    public function delete($conditions = [], $limit = null);

    /**
     * Check if row(s) exists in table
     *
     * @param array|string|Condition $conditions            
     * @return bool
     */
    public function exists($conditions): bool;

    /**
     * Count table rows fitting specified conditions
     *
     * @param array|string|Condition $conditions            
     * @param string|array $columns            
     * @param string|array|null $group            
     * @return int
     */
    public function countRows($conditions = [], $columns = self::COLUMNS_ALL, $group = null): int;

    /**
     * ***************** Utility *******************
     */
    
    /**
     * Escapes column name
     *
     * @param string $column            
     * @return string
     */
    public function escapeColumn(string $column): string;

    /**
     * Get database object
     *
     * @return DatabaseInterface
     */
    public function getDatabase(): DatabaseInterface;

    /**
     * Get table name
     *
     * @param bool $escaped
     *            If table name should be enclosed with quotation corresponding to database driver
     * @return string
     */
    public function getName(bool $escaped = true): string;

    /**
     * Get primary key field name or array of names representing specific column names in database
     *
     * @return string|array
     */
    public function getPkField();

    /**
     * Return list of columns depending on specified filter<br>
     * If no filter specified this function should return array of all table columns in form of ColumnInterface objects with column names as keys
     *
     * @param mixed $filter            
     * @return ColumnInterface[]
     */
    public function getColumns($filter = null): array;

    /**
     * Set array of column objects implementing ColumnInterface
     *
     * @param ColumnInterface[] $columns            
     * @return self
     */
    public function setColumns(array $columns): self;

    /**
     * Format data according to columns config
     *
     * @param array $data
     *            Keys of data array should be corresponding column names (same as in getColumns method)
     * @param bool $defaults
     *            If columns unexisting in $data array should be appended with their default values
     * @return array
     */
    public function format(array $data, bool $defaults = false): array;
}