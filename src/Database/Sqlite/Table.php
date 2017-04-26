<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Sqlite;

use Minwork\Database\Object\AbstractTable;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Object\Column;

/**
 * SQLite implementation of database table
 *
 * @author Christopher Kalkhoff
 *        
 */
class Table extends AbstractTable
{

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Object\AbstractTable::getDbColumns()
     */
    protected function getDbColumns(): array
    {
        $columns = [];
        $sql = $this->getDatabase()->query("PRAGMA table_info({$this->getName()})");
        $result = $sql->fetchAll(Database::FETCH_ASSOC);
        foreach ($result as $column) {
            $c = new Column($column['name'], $column['type'], $column['dflt_value'], ! $column['notnull'], boolval($column['pk']));
            $columns[strval($c)] = $c;
        }
        return $columns;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Object\AbstractTable::getColumnDefinition()
     */
    protected function getColumnDefinition(ColumnInterface $column): string
    {
        // If column is rowid
        if ($column->isAutoIncrement() && $column->isPrimaryKey() && $column->getInternalType() == ColumnInterface::TYPE_INTEGER) {
            return "{$column->getName()} INTEGER PRIMARY KEY AUTOINCREMENT";
        }
        
        $definition = "{$column->getName()} {$column->getType()}";
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
        $result = $this->getDatabase()->exec("DELETE FROM {$this->getName()}");
        $this->getDatabase()->exec("VACUUM");
        return $result;
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
        $columns = $this->getColumns();
        foreach ($columns as $column) {
            $query[strval($column)] = $this->getColumnDefinition($column);
            if ($column->isPrimaryKey()) {
                $pk[strval($column)] = $column;
            }
        }
        if (empty($pk)) {
            throw new \Exception("Table {$this->getName()} doesn't have primary key");
        }
        if (count($pk) === 1) {
            if (! $columns[key($pk)]->isAutoIncrement()) {
                $query[key($pk)] .= ' PRIMARY KEY';
            }
        } elseif (count($pk) > 1) {
            $query[] = 'PRIMARY KEY(' . implode(', ', array_map(function ($column) {
                return $column->getName();
            }, $pk)) . ')';
        } else {
            throw new \Exception('Cannot create table without specifying primary key');
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
     * @see \Minwork\Database\Object\AbstractTable::synchronize()
     */
    public function synchronize(): bool
    {
        // SQLite doesn't support dropping columns so in that case recreate table
        return (count(array_diff_key($this->getDbColumns(), $this->getColumns())) > 0) ? $this->create(true) : parent::synchronize();
    }
}