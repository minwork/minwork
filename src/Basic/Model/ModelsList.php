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
     * Model prototype used to create list of models
     *
     * @var ModelInterface
     */
    protected $prototype;

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
     * @param ModelInterface $prototype            
     * @param Query $query            
     */
    public function __construct(ModelInterface $prototype, Query $query)
    {
        $this->reset()->setQuery($query)->prototype = $prototype;
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

    /**
     * Read models list from storage
     *
     * @param int $page            
     * @param int|null $onPage            
     * @return self
     */
    public function getData(int $page = 1, $onPage = null): self
    {
        $this->page = $page;
        $this->onPage = $onPage;
        $query = $this->query;
        
        if (! is_null($onPage)) {
            $query->setLimit([
                ($page - 1) * $onPage,
                $onPage
            ]);
            $this->total = $this->prototype->getStorage()->count($query);
        }
        
        $list = $this->prototype->getStorage()->get($query);
        
        if (is_null($onPage)) {
            $this->total = count($list);
        }
        
        foreach ($list as $data) {
            $model = clone $this->prototype;
            // Emulate read operation
            /* @var $model \Minwork\Basic\Model\Model */
            $model->getEventDispatcher()->dispatch(new OperationEvent(Read::EVENT_BEFORE));
            $this->list[] = $model->setData($data, false);
            $model->getEventDispatcher()->dispatch(new OperationEvent(Read::EVENT_AFTER));
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
    public function getOnPage()
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