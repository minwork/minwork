<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Object;

use Minwork\Helper\ArrayHelper;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Query;
use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Storage\Interfaces\StorageInterface;
use Minwork\Helper\Formatter;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Utility\Condition;
use Minwork\Basic\Traits\Debugger;

/**
 * Database table
 *
 * @author Christopher Kalkhoff
 *        
 */
abstract class AbstractTable implements TableInterface, DatabaseStorageInterface
{
    use Debugger;

    const COLUMNS_FLAG_NAMES = 2;

    const COLUMNS_FLAG_NAMES_WITHOUT_PRIMARY_KEYS = 4;

    const COLUMNS_FLAG_PRIMARY_KEYS = 8;

    /**
     * Database object
     *
     * @var DatabaseInterface
     */
    protected $database;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var ColumnInterface[]
     */
    protected $columns;

    /**
     *
     * @var string[]
     */
    
    /**
     * Mysql table constructor
     *
     * @param DatabaseInterface $database            
     * @param string $name            
     */
    public function __construct(DatabaseInterface $database, string $name, array $columns = [])
    {
        $this->setDatabase($database)
            ->setName($name)
            ->setColumns($columns);
    }

    protected function setDatabase(DatabaseInterface $database): TableInterface
    {
        $this->database = $database;
        return $this;
    }

    protected function setName(string $name): TableInterface
    {
        $this->name = Formatter::removeQuotes($name);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::getColumns()
     */
    public function getColumns($filter = null): array
    {
        // If columns wasn't specified in constructor load them from database schema
        if (empty($this->columns)) {
            $this->setColumns($this->getDbColumns());
        }
        
        $columns = $this->columns;
        
        if ($filter & static::COLUMNS_FLAG_PRIMARY_KEYS) {
            $columns = array_filter($columns, function ($column) {
                return $column->isPrimaryKey();
            });
        }
        
        if ($filter & static::COLUMNS_FLAG_NAMES) {
            $columns = array_keys($columns);
        }
        
        if ($filter & static::COLUMNS_FLAG_NAMES_WITHOUT_PRIMARY_KEYS) {
            $columns = array_keys(array_filter($columns, function ($column) {
                return ! $column->isPrimaryKey();
            }));
        }
        
        return $columns;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::setColumns()
     */
    public function setColumns(array $columns): TableInterface
    {
        $this->columns = [];
        foreach ($columns as $column) {
            if (! $column instanceof ColumnInterface) {
                throw new \InvalidArgumentException('Columns array element must implement ColumnInterface');
            }
            $this->columns[strval($column)] = $column;
        }
        return $this;
    }

    /**
     * Format data according to columns config<br>
     *
     * @param array $data
     *            Use data keys as column name for formatting values
     * @param bool $defaults
     *            Use default column value if it key is not present in data array
     */
    public function format(array $data, bool $defaults = false): array
    {
        $return = [];
        
        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->columns)) {
                $return[$column] = $this->columns[$column]->format($value);
            } else {
                $return[$column] = $value;
            }
        }
        
        if ($defaults) {
            $toFill = array_keys(array_diff_key($this->columns, $return));
            foreach ($toFill as $column) {
                $columnObj = $this->columns[$column];
                $return[$column] = $columnObj->format($columnObj->getDefaultValue());
            }
        }
        
        return $return;
    }

    /**
     * Get database object
     *
     * @return DatabaseInterface
     */
    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getName($escaped = true): string
    {
        return $escaped ? static::DEFAULT_ESCAPE_CHAR . $this->name . static::DEFAULT_ESCAPE_CHAR : $this->name;
    }

    /**
     *
     * @return ColumnInterface[]
     */
    abstract protected function getDbColumns(): array;

    /**
     * Return column database definition used for table creation and altering
     *
     * @param ColumnInterface $column            
     */
    abstract protected function getColumnDefinition(ColumnInterface $column): string;

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::synchronize()
     */
    public function synchronize(): bool
    {
        $add = [];
        $modify = [];
        $pk = [];
        $query = [];
        
        $dbColumns = $this->getDbColumns();
        $curColumns = $this->getColumns();
        foreach ($curColumns as $name => $column) {
            if (array_key_exists($name, $dbColumns)) {
                if ($dbColumns[$name] != $column) {
                    $modify[strval($column)] = $this->getColumnDefinition($column);
                }
            } else {
                $add[strval($column)] = $this->getColumnDefinition($column);
            }
            
            if ($column->isPrimaryKey()) {
                $pk[strval($column)] = $column;
            }
        }
        $remove = array_diff_key($dbColumns, $curColumns);
        
        if (empty($pk)) {
            $this->debug("Table {$this->getName(false)} doesn't have primary key");
        }
        
        $statement = "ALTER TABLE {$this->getName()} ";
        
        foreach ($add as $name => $definition) {
            $query[] = "ADD {$definition}";
        }
        
        foreach ($modify as $name => $definition) {
            $query[] = "MODIFY COLUMN {$definition}";
        }
        
        foreach ($remove as $name => $column) {
            $query[] = "DROP COLUMN {$column->getName()}";
        }
        
        $curPk = $this->getColumns(self::COLUMNS_FLAG_PRIMARY_KEYS);
        if (array_keys($pk) != array_keys($curPk)) {
            $query[] = 'DROP PRIMARY KEY';
            $query[] = 'ADD PRIMARY KEY(' . implode(', ', array_map(function ($column) {
                return $column->getName();
            }, $pk)) . ')';
        }
        $statement .= implode(', ', $query);
        return $this->getDatabase()->exec($statement) === 0;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::create()
     */
    public function create(bool $replace = false): bool
    {
        $query = [];
        $pk = [];
        foreach ($this->getColumns() as $column) {
            $query[strval($column)] = $this->getColumnDefinition($column);
            if ($column->isPrimaryKey()) {
                $pk[strval($column)] = $column;
            }
        }
        if (empty($pk)) {
            $this->debug("Table {$this->getName(false)} doesn't have primary key");
        }
        if (count($pk) === 1) {
            $query[key($pk)] .= ' PRIMARY KEY';
        } elseif (count($pk) > 1) {
            $query[] = 'PRIMARY KEY(' . implode(',', array_map(function ($column) {
                return $column->getName();
            }, $pk)) . ')';
        }
        
        $statement = "CREATE";
        if ($replace) {
            $statement .= " OR REPLACE TABLE";
        } else {
            $statement .= " TABLE IF NOT EXISTS";
        }
        $statement .= " {$this->getName()} (" . implode(", ", $query) . ")";
        
        $result = $this->getDatabase()->exec($statement);
        return $replace ? $result >= 0 : $result === 0;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::clear()
     */
    public function clear(): int
    {
        return $this->getDatabase()->exec("TRUNCATE TABLE {$this->getName()}");
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::remove()
     */
    public function remove(): bool
    {
        return $this->getDatabase()->exec("DROP TABLE IF EXISTS {$this->getName()}") !== false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::select()
     */
    public function select($conditions = [], $columns = self::COLUMNS_ALL, $order = null, $limit = null, $group = null)
    {
        $statement = "SELECT {$this->prepareColumnsList($columns)} FROM {$this->getName()} ";
        $statement .= ! empty($conditions) ? "{$this->getConditionsQuery($conditions)} " : "";
        $statement .= ! is_null($group) ? "{$this->getGroupQuery($group)} " : "";
        $statement .= ! is_null($order) ? "{$this->getOrderQuery($order)} " : "";
        $statement .= ! is_null($limit) ? "{$this->getLimitQuery($limit)} " : "";
        
        return $this->getDatabase()->query($statement);
    }

    /**
     * Insert into table
     *
     * @param array $values            
     * @return mixed
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            throw new \InvalidArgumentException('Insert values cannot be empty');
        }
        
        $statement = "INSERT INTO {$this->getName()} ";
        if (ArrayHelper::isAssoc($values)) {
            $columns = array_map([
                $this,
                'escapeColumn'
            ], array_keys($values));
            if (! empty($columns)) {
                $statement .= "(" . implode(', ', $columns) . ")";
            }
        }
        $statement .= " VALUES (" . implode(', ', array_map([
            $this->getDatabase(),
            'escape'
        ], $values)) . ")";
        return $this->getDatabase()->query($statement);
    }

    /**
     * Update in table
     *
     * @param array $values            
     * @param array $conditions            
     * @param int|array $limit            
     * @return \PDOStatement|bool
     */
    public function update(array $values, $conditions = [], $limit = null)
    {
        if (empty($values)) {
            throw new \InvalidArgumentException('Update values cannot be empty');
        }
        array_walk($values, function (&$v, $k) {
            $v = "{$this->escapeColumn($k)} = {$this->getDatabase()->escape($v)}";
        });
        $statement = "UPDATE {$this->getName()} SET " . implode(', ', $values) . " ";
        $statement .= ! empty($conditions) ? "{$this->getConditionsQuery($conditions)} " : "";
        $statement .= ! is_null($limit) ? "{$this->getLimitQuery($limit)} " : "";
        
        return $this->getDatabase()->query($statement);
    }

    /**
     * Delete from table
     *
     * @param array $conditions            
     * @param int|array $limit            
     * @return \PDOStatement|bool
     */
    public function delete($conditions = [], $limit = null)
    {
        $statement = "DELETE FROM {$this->getName()} ";
        $statement .= ! empty($conditions) ? "{$this->getConditionsQuery($conditions)} " : "";
        $statement .= ! is_null($limit) ? "{$this->getLimitQuery($limit)} " : "";
        
        return $this->getDatabase()->query($statement);
    }

    /**
     * Check if row(s) exists in table
     *
     * @param array $conditions            
     * @param string $operator            
     * @return bool
     */
    public function exists($conditions): bool
    {
        // If no conditions specified we can't verify if row exists
        if (empty($conditions)) {
            return false;
        } else {
            return boolval($this->getDatabase()
                ->query("SELECT EXISTS(SELECT 1 FROM {$this->getName()} {$this->getConditionsQuery($conditions)} LIMIT 1) as e")
                ->fetchColumn());
        }
    }

    /**
     * Count table rows fitting specified conditions
     *
     * @param array $conditions            
     * @param string $operator            
     * @param string|array $columns            
     * @return int
     */
    public function countRows($conditions = [], $columns = self::COLUMNS_ALL, $group = null): int
    {
        $sql = $this->select($conditions, "COUNT({$this->prepareColumnsList($columns)})", null, null, $group);
        return $sql->fetchColumn();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::getPkField()
     */
    public function getPkField()
    {
        $pkFields = $this->getColumns(self::COLUMNS_FLAG_PRIMARY_KEYS | self::COLUMNS_FLAG_NAMES);
        return count($pkFields) === 1 ? reset($pkFields) : $pkFields;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::getFields()
     */
    public function getFields(): array
    {
        return $this->getColumns(self::COLUMNS_FLAG_NAMES_WITHOUT_PRIMARY_KEYS);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query            
     * @see \MinWork\Storage\Interfaces\DatabaseStorageInterface::get()
     */
    public function get($key)
    {
        $sql = $this->select($key->getConditions(), $key->getColumns(), $key->getOrder(), $key->getLimit());
        $result = $sql->fetchAll(\PDO::FETCH_ASSOC);
        if (! is_array($result)) {
            return null;
        }
        
        $useDefaults = $key->getColumns() === TableInterface::COLUMNS_ALL;
        foreach ($result as &$row) {
            $row = $this->format($row, $useDefaults);
        }
        
        return $key->getLimit() === 1 && is_array($result) && is_array($el = reset($result)) ? $el : $result;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query            
     * @see \Framework\Storage\Interfaces\DatabaseStorageInterface::set()
     */
    public function set($key, $value): StorageInterface
    {
        if ($this->isset($key)) {
            $this->update($value, $key->getConditions(), $key->getLimit());
        } else {
            $fields = $this->getFields();
            $columns = $key->getColumns();
            if ($columns === TableInterface::COLUMNS_ALL) {
                $values = array_combine($fields, $value);
            } elseif (is_array($columns)) {
                $values = array_combine($columns, $value);
            } else {
                throw new \InvalidArgumentException('Insert query columns must be either string indicating all columns (TableInterface::COLUMNS_ALL) or array of column names');
            }
            $this->insert($values);
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query            
     * @see \Framework\Storage\Interfaces\DatabaseStorageInterface::isset()
     */
    public function isset($key): bool
    {
        return $this->exists($key->getConditions());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query            
     * @see \Framework\Storage\Interfaces\DatabaseStorageInterface::unset()
     */
    public function unset($key): StorageInterface
    {
        $this->delete($key->getConditions(), $key->getLimit());
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query            
     * @see \MinWork\Storage\Interfaces\StorageInterface::count()
     */
    public function count($key): int
    {
        return $this->countRows($key->getConditions(), $key->getColumns());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::escapeColumn()
     */
    public function escapeColumn(string $column): string
    {
        $columns = $this->getColumns();
        return array_key_exists($column, $columns) ? $columns[$column]->getName() : static::DEFAULT_ESCAPE_CHAR . $column . static::DEFAULT_ESCAPE_CHAR;
    }

    /**
     * Get LIMIT part of sql query<br>
     * If $limit is array then first element should be offset and second limit
     *
     * @param array|int|string $limit
     *            It can also be an object that is convertable to string
     */
    protected function getLimitQuery($limit): string
    {
        if (is_array($limit) && count($limit) == 2) {
            return "LIMIT {$limit[1]} OFFSET {$limit[0]}";
        } elseif (is_int($limit)) {
            return "LIMIT {$limit}";
        } else {
            return 'LIMIT ' . strval($limit);
        }
    }

    /**
     * Get GROUP BY part of sql query
     *
     * @param array|string $group
     *            It can also be an object that is convertable to string
     * @return string
     */
    protected function getGroupQuery($group): string
    {
        if (is_array($group)) {
            return 'GROUP BY ' . implode(', ', array_map([
                $this,
                'escapeColumn'
            ], $group));
        } elseif (is_string($group) && ctype_alnum($group)) {
            return 'GROUP BY ' . $this->escapeColumn($group);
        } else {
            return 'GROUP BY ' . strval($group);
        }
    }

    /**
     * Example input:<br>
     * <pre>
     * $order = [
     * 'column1',
     * 'column2 DESC',
     * 'column3' => true,
     * 'column4' => -1,
     * 'column5' => 'ASC',
     * ];
     * </pre>
     * Will return 'column1, column2 DESC, "column3" ASC, "column4" DESC, "column5" ASC'
     *
     * @param array|string $order
     *            It can also be an object that is convertable to string
     * @return string
     */
    protected function getOrderQuery($order): string
    {
        if (is_array($order)) {
            $query = [];
            foreach ($order as $key => $value) {
                if (is_string($key)) {
                    if (is_string($value)) {
                        $query[] = ctype_alnum($key) ? "{$this->escapeColumn($key)} {$value}" : "{$key} {$value}";
                    } elseif (is_int($value) || is_bool($value)) {
                        $query[] = "{$this->escapeColumn($key)} " . ($value > 0 ? 'ASC' : 'DESC');
                    } else {
                        $this->debug("Order value part should be either string, integer or boolean - using default sorting for {$key}");
                        $query[] = $this->escapeColumn($key);
                    }
                } elseif (is_int($key) && is_string($value)) {
                    $query[] = ctype_alnum($value) ? $this->escapeColumn($value) : $value;
                }
            }
            return 'ORDER BY ' . implode(', ', $query);
        } elseif (is_string($order)) {
            return 'ORDER BY ' . (ctype_alnum($order) ? $this->escapeColumn($value) : $order);
        }
        return 'ORDER BY ' . strval($order);
    }

    /**
     * Prepare WHERE clausule conditions query string from given params
     *
     * @param array|string|\Minwork\Database\Utility\Condition $conditions
     *            It can also be an object that is convertable to string
     * @return string
     */
    protected function getConditionsQuery($conditions): string
    {
        if (is_string($conditions)) {
            return $conditions;
        } elseif (is_array($conditions)) {
            $query = [];
            $operators = empty($conditions) ? [
                'AND'
            ] : array_fill(0, count($conditions) - 1, 'AND');
            $counter = 0;
            foreach ($conditions as $column => $value) {
                // If condition is string only, concatenate it to query
                if (is_int($column) && is_string($value)) {
                    $query[] = "({$value})";
                } elseif (is_string($column)) {
                    $query[] = "{$this->escapeColumn($column)} {$this->prepareValue($value)}";
                }
                if (count($query) > 0 && count($query) < count($conditions) + count($operators)) {
                    $query[] = "{$operators[$counter++]}";
                }
            }
            return empty($query) ? '' : 'WHERE ' . implode($query, ' ');
        } elseif (is_object($conditions)) {
            if ($conditions instanceof Condition) {
                $conditions->setColumnEscapeFunction([
                    $this,
                    'escapeColumn'
                ])->setValueEscapeFunction([
                    $this->getDatabase(),
                    'escape'
                ]);
            }
            if (method_exists($conditions, '__toString')) {
                return 'WHERE ' . strval($conditions);
            }
        }
        throw new \InvalidArgumentException('Conditions must be either string, array or object convertable to string');
    }

    /**
     * Prepares equal to value query part<br>
     * Single elements are escaped and arrays are parsed to IN(...) format
     *
     * @param mixed $value            
     * @return mixed
     */
    protected function prepareValue($value)
    {
        if (is_string($value) || is_numeric($value) || is_bool($value)) {
            return "= {$this->getDatabase()->escape($value)}";
        } elseif (is_array($value)) {
            return "IN (" . implode(", ", array_map([
                $this->getDatabase(),
                'escape'
            ], $value)) . ")";
        } elseif (is_object($value)) {
            return "= {$this->getDatabase()->escape(serialize($value))}";
        } elseif (is_null($value)) {
            return "IS NULL";
        }
        return $value;
    }

    /**
     * Prepare columns query part<br>
     * If columns are associative array then they should be in form of: [{column_name} => {column_alias}, ...]
     *
     * @param array|string $columns
     *            It can also be an object that is convertable to string
     */
    protected function prepareColumnsList($columns)
    {
        if (is_array($columns)) {
            if (ArrayHelper::isAssoc($columns)) {
                array_walk($columns, function ($v, $k) {
                    return "{$this->escapeColumn($k)} as {$v}";
                });
                return implode(', ', $columns);
            } else {
                return implode(', ', array_map([
                    $this,
                    'escapeColumn'
                ], $columns));
            }
        }
        return strval($columns);
    }
}