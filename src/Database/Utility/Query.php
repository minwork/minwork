<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Utility;

use Minwork\Database\Interfaces\TableInterface;

/**
 * Helper class containing query definition used in table methods
 *
 * @author Christopher Kalkhoff
 *        
 */
class Query
{

    /**
     * Query conditions for WHERE clause
     *
     * @see \Minwork\Database\Object\AbstractTable::getConditionsQuery()
     * @var array|string|\Minwork\Database\Utility\Condition
     */
    protected $conditions;

    /**
     * Columns list used for INSERT, SELECT and UPDATE statements
     *
     * @see \Minwork\Database\Object\AbstractTable::prepareColumnsList()
     * @var array|string|null
     */
    protected $columns;

    /**
     * Statement LIMIT clause
     *
     * @see \Minwork\Database\Object\AbstractTable::getLimitQuery()
     * @var int|array|string|null
     */
    protected $limit;

    /**
     * Statement ORDER BY clause
     *
     * @see \Minwork\Database\Object\AbstractTable::getOrderQuery()
     * @var array|string|null
     */
    protected $order;

    /**
     * Statement GROUP BY clause
     *
     * @see \Minwork\Database\Object\AbstractTable::getGroupQuery()
     * @var array|string|null
     */
    protected $group;

    public function __construct($conditions = [], $columns = null, $limit = null, $order = null, $group = null)
    {
        $this->conditions = $conditions ?? [];
        $this->columns = $columns ?? TableInterface::COLUMNS_ALL;
        $this->limit = $limit;
        $this->order = $order;
        $this->group = $group;
    }

    /**
     * Get query conditions
     *
     * @return array|string|\Minwork\Database\Utility\Condition
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Set query conditions
     *
     * @param array|string|\Minwork\Database\Utility\Condition $conditions
     *            It can also be an object that is convertable to string
     * @return self
     */
    public function setConditions($conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * Get query columns list
     *
     * @return string|array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set query columns list
     *
     * @param array|string $columns
     *            It can also be an object that is convertable to string
     * @return self
     */
    public function setColumns($columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Get query limit
     *
     * @return int|array|string
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set query limit
     *
     * @param int|array|string $limit
     *            It can also be an object that is convertable to string
     * @return self
     */
    public function setLimit($limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Get query order
     *
     * @return array|string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set query order
     *
     * @param array|string $order
     *            It can also be an object that is convertable to string
     * @return self
     */
    public function setOrder($order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get query group
     *
     * @return array|string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set query group
     *
     * @param array|string $group
     *            It can also be an object that is convertable to string
     * @return self
     */
    public function setGroup($group): self
    {
        $this->group = $group;
        return $this;
    }
}