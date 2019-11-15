<?php

namespace Minwork\Database\Utility;

use Minwork\Database\Interfaces\QueryInterface;
use Minwork\Database\Interfaces\TableInterface;

class QueryBuilder
{
    private $storage;

    public function __construct(TableInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return TableInterface
     */
    public function getStorage(): TableInterface
    {
        return $this->storage;
    }

    public function select($columns = null): self
    {

    }

    public function from(TableInterface $table): self
    {

    }

    public function join(string $selfAlias, TableInterface $joinedTable, string $joinedAlias, $conditions): self
    {

    }

    public function where($conditions): self
    {

    }

    public function orderBy($order): self
    {

    }

    public function groupBy($order): self
    {

    }

    public function limit(int $amount, ?int $offset = null)
    {

    }

    public static function selectFromQuery(QueryInterface $query): self
    {

    }

    public function getSql(bool $preparedStatement = true): string
    {

    }
}