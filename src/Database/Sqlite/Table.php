<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Sqlite;

use Exception;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Prototypes\AbstractTable;

/**
 * SQLite implementation of database table
 *
 * @author Christopher Kalkhoff
 *        
 */
class Table extends AbstractTable
{
    public function escapeColumn(string $column): string
    {
        return "\"{$column}\"";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Prototypes\AbstractTable::getDbColumns()
     */
    protected function getDbColumns(): array
    {
        $columns = [];
        $sql = $this->getDatabase()->query("PRAGMA table_info({$this->getName()})");
        $result = $sql->fetchAll(Database::FETCH_ASSOC);
        foreach ($result as $column) {
            $c = Column::createFromDefinition($column);
            $columns[strval($c)] = $c;
        }
        return $columns;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Prototypes\AbstractTable::getColumnDefinition()
     */
    protected function getColumnDefinition(ColumnInterface $column): string
    {
        // If column is rowid
        if ($column->isAutoIncrement() && $column->isPrimaryKey()) {
            return "{$this->escapeColumn($column->getName())} INTEGER PRIMARY KEY AUTOINCREMENT";
        }
        
        $definition = "{$this->escapeColumn($column->getName())} {$column->getDatabaseType()}";
        $definition .= $column->isNullable() ? " NULL" : " NOT NULL";
        
        if (is_null($column->getDefaultValue()) && $column->isNullable()) {
            $definition .= " DEFAULT NULL";
        } elseif (! is_null($column->getDefaultValue())) {
            $definition .= ' DEFAULT ' . $this->getDatabase()->escape(strval($column->getDefaultValue()));
        }
        
        return $definition;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\MySql\Table::clear()
     */
    public function clear(): int
    {
        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlWithoutWhere */
        $result = $this->getDatabase()->exec("DELETE FROM {$this->getName()}");
        $this->getDatabase()->exec("VACUUM");
        return $result;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @throws Exception
     * @see \Minwork\Database\Interfaces\TableInterface::create()
     */
    public function create(bool $replace = false): bool
    {
        $query = [];
        $pk = [];
        $columns = $this->getColumns();
        foreach ($columns as $column) {
            $query[$column->getName()] = $this->getColumnDefinition($column);
            if ($column->isPrimaryKey()) {
                $pk[$column->getName()] = $column;
            }
        }
        if (empty($pk)) {
            throw new Exception("Table {$this->getName()} doesn't have primary key");
        }
        if (count($pk) === 1) {
            if (! $columns[key($pk)]->isAutoIncrement()) {
                $query[key($pk)] .= ' PRIMARY KEY';
            }
        } elseif (count($pk) > 1) {
            $query[] = 'PRIMARY KEY(' . implode(', ', array_map(function ($column) {
                /** @var ColumnInterface $column */
                return $this->escapeColumn($column->getName());
            }, $pk)) . ')';
        } else {
            throw new Exception('Cannot create table without specifying primary key');
        }
        
        $statement = "CREATE";
        if ($replace) {
            $this->remove();
            $statement .= " TABLE";
        } else {
            $statement .= " TABLE IF NOT EXISTS";
        }
        $statement .= " {$this->getName()} (" . implode(",", $query) . ")";
        $result = $this->getDatabase()->exec($statement);
        return $replace ? $result >= 0 : $result === 0;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @throws Exception
     * @see \Minwork\Database\Prototypes\AbstractTable::synchronize()
     */
    public function synchronize(): bool
    {
        // SQLite doesn't support dropping columns so in that case recreate table
        return (count(array_diff_key($this->getDbColumns(), $this->getColumns())) > 0) ? $this->create(true) : parent::synchronize();
    }

    public static function getColumnInstance(string $name, string $type, $defaultValue = null, bool $nullable = false, bool $primaryKey = false, bool $autoIncrement = false): Column
    {
        return new Column($name, $type, $defaultValue, $nullable, $primaryKey, $autoIncrement);
    }
}