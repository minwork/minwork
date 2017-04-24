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

/**
 * List of models according to supplied prototype
 * @author Christopher Kalkhoff
 *
 */
class ModelsList
{
    /**
     * Page number
     * @var int
     */
    public $page;

    /**
     * Elements on page
     * @var int
     */
    public $onPage;

    /**
     * Total elements fitting supplied query
     * @var int
     */
    public $total;

    /**
     * Model prototype used to create list of models
     * @var ModelInterface
     */
    protected $prototype;

    /**
     * Query used for selecting models data from storage
     * @var Query
     */
    protected $query;

    /**
     * List of models
     * @var array
     */
    protected $list;

    public function __construct(ModelInterface $prototype, Query $query)
    {
        $this->reset()->setQuery($query)->prototype = $prototype;
    }

    /**
     * Reset to initial properties values
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
     * Set list query
     * @param Query $query
     * @return self
     */
    public function setQuery(Query $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Read list of models
     * @param int $page
     * @param int $onPage
     * @param bool $refresh
     * @return self
     */
    public function getData(int $page = 1, int $onPage = null): self
    {
        $this->page = $page;
        $this->onPage = $onPage;
        $query = $this->query;
        $this->total = $this->prototype->getStorage()
            ->count($query);
        
        if (!is_null($onPage)) {
            $query->limit = [
                ($page - 1) * $onPage,
                $onPage
            ];
        }
        $list = $this->prototype->getStorage()
            ->get($query);
        
        foreach ($list as $data) {
            $model = clone $this->prototype;
            $idField = $this->prototype->getStorage()->getPkField();
            $id = is_array($idField) ? array_intersect_key($data, array_flip($idField)) : $data[$idField];
            $this->list[] = $model->setId($id)->setData($data);
        }
        
        return $this;
    }

    /**
     * Get list of models
     * @return array
     */
    public function getElements(): array
    {
        return $this->list;
    }
}