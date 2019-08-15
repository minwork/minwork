<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Doctrine;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use InvalidArgumentException;
use Minwork\Basic\Traits\Debugger;
use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Condition;
use Minwork\Database\Utility\Query;
use Minwork\Helper\Arr;
use Minwork\Helper\Formatter;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Storage\Interfaces\StorageInterface;
use Throwable;

class Table implements TableInterface, DatabaseStorageInterface
{
    use Debugger;

    /**
     * Database object
     *
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * Table name in database
     *
     * @var string
     */
    protected $name;

    /**
     * List of table columns
     *
     * @var Column[]
     */
    protected $columns;

    /**
     * @var DoctrineTable
     */
    protected $details;

    /**
     * @var array
     */
    protected $primaryKeys = [];

    /**
     *
     * @param Database $database
     * @param string $name
     * @param Column[] $columns
     */
    public function __construct(Database $database, string $name, array $columns = [])
    {
        $this
            ->setDatabase($database)
            ->setDetails($this->getSchemaManager()->listTableDetails($database->getConnection()->quoteIdentifier(Formatter::removeQuotes($name))))
            ->setColumns($columns);
    }

    protected function getSchemaManager(): AbstractSchemaManager
    {
        return $this->getDatabase()->getConnection()->getSchemaManager();
    }

    /**
     * Set database object
     *
     * @param DatabaseInterface $database
     * @return TableInterface|self
     */
    protected function setDatabase(DatabaseInterface $database): Table
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @param DoctrineTable $details
     * @return Table
     */
    protected function setDetails(DoctrineTable $details): Table
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return DoctrineTable
     */
    public function getDetails(): DoctrineTable
    {
        return $this->details;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @return Column[]
     * @see \Minwork\Database\Interfaces\TableInterface::getColumns()
     */
    public function getColumns($filter = null): array
    {
        // If columns wasn't specified in constructor load them from database schema
        if (empty($this->columns)) {
            $this->setColumns($this->getDetails()->getColumns());
        }

        $columns = $this->columns;

        if ($filter & static::COLUMNS_NO_PK) {
            $columns = Arr::filterByKeys($columns, $this->getPrimaryKeyColumns(), true);
        } elseif ($filter & static::COLUMNS_PK_ONLY) {
            $columns = Arr::filterByKeys($columns, $this->getPrimaryKeyColumns());
        }

        if ($filter & self::COLUMN_NAMES) {
            return array_keys($columns);
        }

        return $columns;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::setColumns()
     * @param Column[] $columns
     */
    public function setColumns(array $columns): TableInterface
    {
        $this->columns = [];
        $primaryKeys = [];
        foreach ($columns as $column) {
            if (! $column instanceof Column) {
                throw new InvalidArgumentException('Doctrine table must use appropriate doctrine columns');
            }
            $name = $column->getName();
            $this->columns[$name] = $column;
            if ($column->isPrimaryKey()) {
                $primaryKeys[] = $name;
            }
        }

        $this->setPrimaryKeys($primaryKeys);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @throws DBALException
     * @see \Minwork\Database\Interfaces\TableInterface::format()
     */
    public function format(array $data, bool $defaults = false): array
    {
        $return = [];
        $columns = $this->getColumns();
        $database = $this->getDatabase();

        foreach ($data as $column => $value) {
            if (array_key_exists($column, $columns)) {
                $return[$column] = $columns[$column]->format($value, $database);
            } else {
                $return[$column] = $value;
            }
        }

        if ($defaults) {
            $toFill = array_keys(array_diff_key($columns, $return));
            foreach ($toFill as $column) {
                $columnObj = $columns[$column];
                $return[$column] = $columnObj->format($columnObj->getDefaultValue(), $database);
            }
        }

        return $return;
    }

    /**
     * @return Database
     */
    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::getName()
     */
    public function getName(bool $quoted = true): string
    {
        if ($quoted) {
            try {
                return $this->getDetails()->getQuotedName($this->getDatabase()->getConnection()->getDatabasePlatform());
            } catch (DBALException $e) {
                return $this->getDatabase()->getConnection()->quoteIdentifier($this->getDetails()->getName());
            }
        } else {
            return $this->getDetails()->getName();
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::synchronize()
     */
    public function synchronize(): bool
    {
        $comparator = new Comparator();
        $newTable = clone $this->getDetails();

        $currentColumns = $this->getDetails()->getColumns();
        $newColumns = $this->getColumns();

        foreach ($newColumns as $name => $col) {
            $column = $col->getDoctrineColumn();
            if ($newTable->hasColumn($name)) {
                $newTable->changeColumn($name, $column->toArray());
            } else {
                $newTable->addColumn($name, $col->getType(), $column->toArray());
            }
        }
        // Remove unused columns
        foreach (array_diff_key($currentColumns, $newColumns) as $name => $column) {
            $newTable->dropColumn($name);
        }

        $primaryKeyColumns = $this->getPrimaryKeyColumns();
        try {
            $currentPrimaryKeyColumns = $this->getDetails()->getPrimaryKeyColumns();
        } catch (Throwable $exception) {
            $currentPrimaryKeyColumns = [];
        }

        // If primary key changed then update
        if (!empty(array_diff($currentPrimaryKeyColumns, $primaryKeyColumns))) {
            $newTable->dropPrimaryKey();
            // Set primary keys for new table
            $newTable->setPrimaryKey($primaryKeyColumns);
        }

        // Synchronize
        $diff = $comparator->diffTable($this->getDetails(), $newTable);

        if ($diff) {
            $this->getSchemaManager()->alterTable($diff);
            return true;
        }

        return false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::create()
     */
    public function create(bool $replace = false): bool
    {
        $manager = $this->getSchemaManager();
        try {
            $table = new DoctrineTable($this->getName(), Arr::mapObjects($this->getColumns(), 'getDoctrineColumn'));
        } catch (DBALException $e) {
            return false;
        }

        $table->setPrimaryKey($this->getPrimaryKeyColumns());

        if ($replace) {
            $manager->dropAndCreateTable($table);
        } else {
            $manager->createTable($table);
        }

        $this->setDetails($table);

        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::clear()
     */
    public function clear(): int
    {
        $connection = $this->getDatabase()->getConnection();
        try {
            $platform = $connection->getDatabasePlatform();
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $affected = $connection->exec($platform->getTruncateTableSql($this->getName()));
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            
            return $affected;
        } catch (DBALException $exception) {
            return 0;
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::remove()
     */
    public function remove(): bool
    {
        $this->getSchemaManager()->dropTable($this->getName());
        return true;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::select()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @see \Minwork\Database\Object\AbstractTable::prepareColumnsList()
     * @see \Minwork\Database\Object\AbstractTable::getOrderQuery()
     * @see \Minwork\Database\Object\AbstractTable::getLimitQuery()
     * @see \Minwork\Database\Object\AbstractTable::getGroupQuery()
     */
    public function select($conditions = [], $columns = self::COLUMNS_ALL, $order = null, $limit = null, $group = null)
    {
        $queryBuilder = $this->getDatabase()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select($this->prepareColumnsList($columns))
            ->from($this->getName());

        $this
            ->appendConditions($queryBuilder, $conditions)
            ->appendOrder($queryBuilder, $order)
            ->appendLimit($queryBuilder, $limit)
            ->appendGroup($queryBuilder, $group);

        return $queryBuilder->execute();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::insert()
     */
    public function insert(array $values)
    {
        if (empty($values) || !Arr::isAssoc($values)) {
            throw new InvalidArgumentException('Insert values must be associative array');
        }

        $builder = $this->getDatabase()->getConnection()->createQueryBuilder()->insert($this->getName());

        foreach ($values as $column => $value) {
            $builder->setValue($this->escapeColumn($column), $builder->createNamedParameter($value));
        }

        return $builder->execute();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::update()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @see \Minwork\Database\Object\AbstractTable::getLimitQuery()
     */
    public function update(array $values, $conditions = [], $limit = null)
    {
        if (empty($values) || !Arr::isAssoc($values)) {
            throw new InvalidArgumentException('Update values must be associative array');
        }

        $builder = $this->getDatabase()->getConnection()->createQueryBuilder()->update($this->getName());

        foreach ($values as $column => $value) {
            $builder->set($this->escapeColumn($column), $builder->createNamedParameter($value));
        }

        $this->appendConditions($builder, $conditions)->appendLimit($builder, $limit);

        return $builder->execute();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::delete()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @see \Minwork\Database\Object\AbstractTable::getLimitQuery()
     */
    public function delete($conditions, $limit = null)
    {
        $builder = $this->getDatabase()->getConnection()->createQueryBuilder()->delete($this->getName());

        $this->appendConditions($builder, $conditions);

        if (!is_null($limit)) {
            $this->appendLimit($builder, $limit);
        }

        return $builder->execute();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::exists()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     */
    public function exists($conditions): bool
    {
        // If no conditions specified we can't verify if row exists
        if (empty($conditions)) {
            return false;
        } else {
            $builder = $this->getDatabase()->getConnection()->createQueryBuilder()->select(1)->from($this->getName())->setMaxResults(1);
            $this->appendConditions($builder, $conditions);

            return boolval($builder->execute()->fetchColumn());
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\TableInterface::countRows()
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @see \Minwork\Database\Object\AbstractTable::prepareColumnsList()
     * @see \Minwork\Database\Object\AbstractTable::getGroupQuery()
     */
    public function countRows($conditions = [], $columns = self::COLUMNS_ALL, $group = null): int
    {
        $sql = $this->select($conditions, "COUNT({$this->prepareColumnsList($columns)})", null, null, $group);
        return $sql->fetchColumn();
    }

    /**
     * Get array of primary keys column names
     * @return string[]
     */
    protected function getPrimaryKeyColumns(): array
    {
        return $this->primaryKeys;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::getPrimaryKey()
     */
    public function getPrimaryKey()
    {
        $pkFields = $this->getPrimaryKeyColumns();
        return count($pkFields) === 1 ? reset($pkFields) : $pkFields;
    }

    public function setPrimaryKeys($columns): TableInterface
    {
        $this->primaryKeys = $columns;
        return $this;
    }


    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::getFields()
     */
    public function getFields(): array
    {
        return $this->getColumns(self::COLUMNS_NO_PK | self::COLUMN_NAMES);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param $key Query
     * @throws DBALException
     * @see \MinWork\Storage\Interfaces\DatabaseStorageInterface::get()
     */
    public function get($key)
    {
        $statement = $this->select($key->getConditions(), $key->getColumns(), $key->getOrder(), $key->getLimit());


        $result = $statement->fetchAll(FetchMode::ASSOCIATIVE);

        if (! is_array($result)) {
            return null;
        }

        $useDefaults = $key->getColumns() === self::COLUMNS_ALL;

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
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::set()
     */
    public function set($key, $value): StorageInterface
    {
        if ($this->isset($key)) {
            $this->update($value, $key->getConditions(), $key->getLimit());
        } else {
            $fields = $this->getFields();
            $columns = $key->getColumns();
            if ($columns === self::COLUMNS_ALL) {
                $values = array_combine($fields, $value);
            } elseif (is_array($columns)) {
                $values = array_combine($columns, $value);
            } else {
                throw new InvalidArgumentException('Insert query columns must be either string indicating all columns (Table::COLUMNS_ALL) or array of column names');
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
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::isset()
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
     * @see \Minwork\Storage\Interfaces\DatabaseStorageInterface::unset()
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
        return $this->countRows($key->getConditions(), $key->getColumns(), $key->getGroup());
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
        if (array_key_exists($column, $columns)) {
            try {
                return $columns[$column]->getDoctrineColumn()->getQuotedName($this->getDatabase()->getConnection()->getDatabasePlatform());
            } catch (DBALException $e) {

            }
        }
        return $this->getDatabase()->getConnection()->quoteIdentifier($column);
    }

    protected function appendLimit(QueryBuilder $builder, $limit): self
    {
        if (is_array($limit) && count($limit) == 2) {
            $builder->setMaxResults($limit[1])->setFirstResult($limit[0]);
        } elseif (!is_null($limit)) {
            $builder->setMaxResults(intval($limit));
        }

        return $this;
    }

    protected function appendGroup(QueryBuilder $builder, $group): self
    {
        if (is_array($group)) {
            $builder->groupBy($group);
        } elseif (!is_null($group)) {
            $builder->groupBy(strval($group));
        }

        return $this;
    }

    protected function appendOrder(QueryBuilder $builder, $order): self
    {
        if (is_array($order)) {
            foreach ($order as $key => $value) {
                if (is_string($key)) {
                    if (is_string($value)) {
                        $builder->addOrderBy($key, $value);
                    } elseif (is_int($value) || is_bool($value)) {
                        $builder->addOrderBy($key,$value > 0 ? 'ASC' : 'DESC');
                    } else {
                        $this->debug("Order value part should be either string, integer or boolean - using default sorting for {$key}");
                        $builder->addOrderBy($key);
                    }
                } elseif (is_int($key) && is_string($value)) {
                    $builder->addOrderBy($value);
                }
            }
        } elseif (!is_null($order)) {
            $builder->orderBy($order);
        }

        return $this;
    }

    protected function appendConditions(QueryBuilder $builder, $conditions): self
    {
        if (is_array($conditions) && !empty($conditions)) {
            foreach ($conditions as $column => $value) {
                if (is_int($column) && is_string($value)) {
                    $builder->andWhere($value);
                } elseif(is_string($column)) {
                    $this->prepareValue($builder, $this->escapeColumn($column), $value);
                }
            }
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
                $builder->where(strval($conditions));
            }
        }

        return $this;
    }

    /**
     * Prepare column value for conditions defined by array<br>
     * Single elements are escaped and arrays are parsed to IN(...) format
     *
     * @param QueryBuilder $builder
     * @param string $column
     * @param mixed $value
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     */
    protected function prepareValue(QueryBuilder $builder, string $column, $value)
    {
        if (is_int($value)) {
            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter($value, ParameterType::INTEGER)));
        } elseif (is_bool($value)) {
            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter($value, ParameterType::BOOLEAN)));
        } elseif (is_string($value) || is_numeric($value)) {
            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter($value, ParameterType::STRING)));
        } elseif (is_array($value)) {
            $builder->andWhere(
                $builder->expr()->in($column, array_map(function ($value) use ($builder) {
                    return $builder->createNamedParameter($value);
                }, $value))
            );
        } elseif (is_object($value)) {
            $builder->andWhere($builder->expr()->eq($column, $builder->createNamedParameter(serialize($value), ParameterType::LARGE_OBJECT)));
        } elseif (is_null($value)) {
            $builder->andWhere($builder->expr()->isNull($column));
        }
    }

    /**
     * Prepare columns query part<br>
     * If columns are associative array then they should be in form of: [{column_name} => {column_alias}, ...]
     *
     * @param array|string $columns
     *            It can also be an object that is convertable to string
     * @return string
     */
    protected function prepareColumnsList($columns)
    {
        if (is_array($columns)) {
            if (Arr::isAssoc($columns)) {
                array_walk($columns, function ($v, $k) {
                    return "{$this->escapeColumn($k)} as {$this->escapeColumn($v)}";
                });
            }
        }/* elseif ($columns === self::COLUMNS_ALL) {
            return null;
        }*/
        return $columns;
    }
}