<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Model;

use Exception;
use InvalidArgumentException;
use Minwork\Basic\Interfaces\BindableModelInterface;
use Minwork\Database\Utility\Query;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;

/**
 * Used for n to n relation on models
 * 
 * @author Christopher Kalkhoff
 */
class ModelBinder extends Model
{

    /**
     *
     * @var BindableModelInterface[]
     */
    protected $models = [];

    /**
     *
     * @param DatabaseStorageInterface $storage            
     * @param BindableModelInterface[] $models
     * @param bool $buffering
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(DatabaseStorageInterface $storage, array $models = [], bool $buffering = true, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->models = $models;
        parent::__construct($storage, self::getModelBinderId($models), $buffering, $eventDispatcher);
    }

    /**
     * Set models used for computing id fields
     * 
     * @param BindableModelInterface[] $models            
     * @return self
     */
    public function setModels(array $models): self
    {
        $this->models = $models;
        $this->setId(self::getModelBinderId($models));

        return $this;
    }
    
    public static function getModelBinderId(array $models): array
    {
        $idFields = [];
        $id = [];
        
        foreach ($models as $model) {
            if (!$model instanceof BindableModelInterface) {
                throw new InvalidArgumentException('Models must implement BindableModelInterface');
            }
            $idFields[spl_object_hash($model)] = $model->getBindingFieldName();
        }
        
        foreach (array_count_values($idFields) as $value => $count) {
            if ($count > 1) {
                $keys = array_keys($idFields, $value, true);
                for ($i = 1; $i <= $count; $i ++) {
                    $key = $keys[$i - 1];
                    $idFields[$key] = "{$idFields[$key]}_{$i}";
                }
            }
        }
        
        foreach ($models as $model) {
            /* @var $model BindableModelInterface */
            $modelId = $model->getId();
            if (is_null($modelId)) {
                throw new InvalidArgumentException('Cannot use Model without id as one of ModelBinder arguments');
            }
            $id[$idFields[spl_object_hash($model)]] = $modelId;
        }
        
        return $id;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::getId()
     */
    public function getId(?string $key = null)
    {
        if ($this->state === self::STATE_CREATE) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->synchronize();
        }
        return is_null($key) ? $this->id : ($this->id[$key] ?? null);
    }

    /**
     * Execute actions that syncs storage with model data
     *
     * @return bool
     * @throws Exception
     */
    public function synchronize(): bool
    {
        if ($this->requireAction()) {
            $state = $this->state;
            $this->state = self::STATE_NOP;
            switch ($state) {
                case self::STATE_CREATE:
                    $insertData = $this->getChangedData();
                    $insertData = array_merge($insertData, $this->getId());
                    $this->getStorage()->set(new Query([], array_keys($insertData)), array_values($insertData));
                    $this->exists = true;
                    return true;
                    break;
                case self::STATE_UPDATE:
                    if ($updateData = $this->getChangedData()) {
                        $this->getStorage()->set(new Query($this->getQueryConditionsWithId()), $updateData);
                    }
                    return true;
                    break;
            }
        }
        
        return false;
    }
}