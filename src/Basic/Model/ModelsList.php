<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Model;

use Minwork\Basic\Interfaces\ModelInterface;
use Minwork\Database\Utility\Query;
use Minwork\Operation\Basic\Read;
use Minwork\Operation\Object\OperationEvent;
use Minwork\Helper\ArrayHelper;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;

/**
 * List of models according to supplied prototype
 *
 * @author Christopher Kalkhoff
 *        
 */
class ModelsList
{

    /**
     * Page number
     *
     * @var int
     */
    protected $page;

    /**
     * Elements on page (if null then all results must be returned on a single page)
     *
     * @var int|null
     */
    protected $onPage;

    /**
     * Total elements fitting supplied query
     *
     * @var int
     */
    protected $total;

    /**
     * Used to create single model on list.<br>
     * Can be either Model object, callable which returns Model or array of callable and it's arguments.<br>
     * In case of callable first argument will be id 
     *
     * @var ModelInterface|callable|array
     */
    protected $prototype;
    
    protected $storage;

    /**
     * Query used for selecting models data from storage
     *
     * @var Query
     */
    protected $query;

    /**
     * List of models
     *
     * @var ModelInterface[]
     */
    protected $list;

    /**
     *
     * @param ModelInterface|callable|array $prototype            
     * @param Query $query            
     */
    public function __construct($prototype, Query $query, DatabaseStorageInterface $storage = null)
    {
        $this->reset()->setPrototype($prototype)->setQuery($query)->setStorage($storage);
    }

    /**
     * Reset to initial properties values
     *
     * @return self
     */
    public function reset(): self
    {
        $this->page = null;
        $this->onPage = null;
        $this->total = 0;
        $this->list = [];
        return $this;
    }

    /**
     * Set query used for getting models list from storage
     *
     * @param Query $query            
     * @return self
     */
    public function setQuery(Query $query): self
    {
        $this->query = $query;
        return $this;
    }
    
    public function setStorage(?DatabaseStorageInterface $storage): self
    {
        $this->storage = is_null($storage) ? $this->getModel()->getStorage() : $storage;
        return $this;
    }
    
    public function setPrototype($prototype): self
    {
        $this->prototype = $prototype;
        return $this;
    }
    
    protected function getModel(?array $data = null): ModelInterface
    {
        if (is_object($this->prototype) && $this->prototype instanceof ModelInterface) {
            $model = clone $this->prototype;
        } elseif (is_callable($this->prototype)) {
            // As argument pass id if default, full data or null
            $model = ($this->prototype)($data[TableInterface::DEFAULT_PK_FIELD] ?? $data);
        } elseif (is_array($this->prototype) && is_callable($this->prototype[0])) {
            $function = array_shift($this->prototype);
            $arguments = $this->prototype;
            if (! is_null($data)) {
                array_unshift($arguments, $data[TableInterface::DEFAULT_PK_FIELD] ?? $data);
            }
            $model = $function(...$arguments);
        } else {
            throw new \InvalidArgumentException('Model prototype must be either ModelInterface object, callable or array containing callable and its arguments');
        }
        
        if (! is_null($data)) {
            $model->initFromData($data);
        }
        
        return $model;
    }

    /**
     * Read models list from storage
     *
     * @param int $page            
     * @param int|null $onPage            
     * @return self
     */
    public function getData(int $page = 1, ?int $onPage = null): self
    {
        $this->page = $page;
        $this->onPage = $onPage;
        $query = $this->query;
        
        if (! is_null($onPage)) {
            $countQuery = clone $query;
            $countQuery->setColumns(null);
            $this->total = $this->storage->count($countQuery);

            $query->setLimit([
                min(($page - 1) * $onPage, ceil($this->total / $onPage)),
                $onPage
            ]);
        }
        
        $list = $this->storage->get($query);
        
        if (is_null($onPage)) {
            $this->total = count($list);
        }
        
        foreach ($list as $data) {
            $this->list[] = $this->getModel($data);
        }
        
        return $this;
    }

    /**
     * Get list of models
     *
     * @return ModelInterface[]
     */
    public function getElements(): array
    {
        return $this->list;
    }

    /**
     * Get current page number
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get number of models per page
     *
     * @return int|null
     */
    public function getOnPage(): ?int
    {
        return $this->onPage;
    }

    /**
     * Get total amount of results
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}