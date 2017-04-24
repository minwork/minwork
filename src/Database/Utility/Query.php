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
 * @author Christopher Kalkhoff
 *
 */
class Query 
{
    public $conditions, $columns, $limit, $order, $group;
    public function __construct($conditions = [], $columns = TableInterface::COLUMNS_ALL, $limit = null, $order = null, $group = null) {
        $this->conditions = $conditions ?? [];
        $this->columns = $columns ?? TableInterface::COLUMNS_ALL;
        $this->limit = $limit;
        $this->order = $order;
        $this->group = $group;
    }
}