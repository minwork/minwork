<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\MySql;

use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Prototypes\AbstractTable;
use Minwork\Helper\Formatter;

/**
 * MySQL implementation of database table
 *
 * @author Christopher Kalkhoff
 *        
 */
class Table extends AbstractTable
{
    public function escapeColumn(string $column): string
    {
        return "`$column`";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Prototypes\AbstractTable::setName()
     */
    protected function setName(string $name): TableInterface
    {
        $this->name = Formatter::removeQuotes($name);
        return $this;
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
        /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlResolve */
        $result = $this->getDatabase()
            ->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA={$this->getDatabase()->escape($this->getDatabase()->getName())} AND TABLE_NAME={$this->getDatabase()->escape($this->getName(false))}")
            ->fetchAll(Database::FETCH_ASSOC);
        foreach ($result as $column) {
            //$column['CHARACTER_SET_NAME'] ?? ''
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
        $definition = "{$this->escapeColumn($column->getName())} {$column->getDatabaseType()}";
        $definition .= $column->isNullable() ? " NULL" : " NOT NULL";
        
        if (is_null($column->getDefaultValue()) && $column->isNullable()) {
            $definition .= " DEFAULT NULL";
        } elseif (! is_null($column->getDefaultValue())) {
            $definition .= ' DEFAULT ' . $this->getDatabase()->escape(strval($column->getDefaultValue()));
        } elseif ($column->isAutoIncrement()) {
            $definition .= ' AUTO_INCREMENT';
        }
        
        return $definition;
    }
}