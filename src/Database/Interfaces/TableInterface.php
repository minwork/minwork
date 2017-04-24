<?php
namespace Minwork\Database\Interfaces;

interface TableInterface
{

    const DEFAULT_PK_FIELD = 'id';

    const DEFAULT_ESCAPE_CHAR = '"';

    const COLUMNS_ALL = '*';

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    /******************** Table operations ********************/
    
    /**
     * Create table based on schema
     *
     * @param bool $replace            
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
     * Synchronize database table with current table schema
     *
     * @return bool If synchronization was successfull
     */
    public function synchronize(): bool;
    
    /******************** Table queries ********************/

    /**
     * Select rows from table
     *
     * @see Minwork\Database\MySql\AbstractTable\AbstractTable::getConditionsQuery
     * @see Minwork\Database\MySql\AbstractTable\AbstractTable::getOrderQuery
     * @see Minwork\Database\MySql\AbstractTable\AbstractTable::getLimitQuery
     * @param array $conditions
     *            Select conditions (typically WHERE part)
     * @param string $operator            
     * @param string|array $columns            
     * @param string|array $order            
     * @param string|array $limit            
     * @param string|array $group            
     * @return mixed
     */
    public function select($conditions = [], $columns = self::COLUMNS_ALL, $limit = null, $order = null, $group = null);

    /**
     * Insert into table<br>
     * Values can have forms like
     * <pre>
     * [true, 'value2', 3, null, ...]
     * ['id' => 5, 'new' => true, 'data' => 'test', 'changed_date' => null, ...]
     * </pre>
     *
     * @param array $values
     *            Array of values only or column name as a key and corresponding value
     * @return mixed
     */
    public function insert(array $values);

    /**
     * Update row(s) in table
     *
     * @param array $values
     *            Array of values only or column name as a key and corresponding value
     * @param array $conditions            
     * @param string $operator            
     * @param int|array $limit            
     * @return mixed
     */
    public function update(array $values, $conditions = [], $limit = null);

    /**
     * Delete row(s) from table
     *
     * @param array $conditions            
     * @param string $operator            
     * @param int|array $limit            
     * @return mixed
     */
    public function delete($conditions = [], $limit = null);

    /**
     * Check if row(s) exists in table
     *
     * @param array $conditions            
     * @param string $operator            
     * @return bool
     */
    public function exists($conditions): bool;

    /**
     * Count table rows fitting specified conditions
     *
     * @param array $conditions            
     * @param string $operator            
     * @param string|array $columns            
     * @param string|array $group            
     * @return int
     */
    public function countRows($conditions = [], $columns = self::COLUMNS_ALL, $group = null): int;
    
    /******************** Utility ********************/
    
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
     * @param $escaped If
     *            table name should be enclosed with quotation corresponding to database driver
     * @return string
     */
    public function getName($escaped = true): string;
    
    /**
     * Get primary key field name or array of names representing specific column names in database
     *
     * @return string|array
     */
    public function getPkField();
    
    /**
     * Return list of columns depending on specified filter<br>
     * If no filter specified this function should return array of ColumnInterface objects with column names as keys
     *
     * @return \Minwork\Database\Interfaces\ColumnInterface[]
     */
    public function getColumns($filter = null): array;
    
    /**
     * Set array of column objects implementing ColumnInterface
     * @param ColumnInterface[] $columns
     * @return self
     */
    public function setColumns(array $columns): self;
    
    /**
     * Format data according to columns config
     *
     * @param array $data
     *            Use data keys as column name for formatting values
     * @param bool $defaults
     *            Use default column value if it key is not present in data array
     */
    public function format(array $data, bool $defaults = false): array;
    
    
}