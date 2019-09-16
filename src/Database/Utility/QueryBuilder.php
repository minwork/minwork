<?php

namespace Minwork\Database\Utility;

use Minwork\Storage\Interfaces\DatabaseStorageInterface;

class QueryBuilder
{
    private $alias;
    private $storage;

    public function __construct(DatabaseStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public static function build(DatabaseStorageInterface $storage): self
    {
        return new self($storage);
    }

    public function select($columns = null)
    {

    }
}