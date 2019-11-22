<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Model;

use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Database\Utility\Query;
use Minwork\Basic\Interfaces\BindableModelInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;

/**
 * Used for n to n relation on models
 * 
 * @author Christopher Kalkhoff
 */
class ModelBinder extends Model
{
    /**
     *
     * @param DatabaseStorageInterface $storage            
     * @param BindableModelInterface[] $ids
     * @param bool $buffering
     * @param EventDispatcherInterface $eventDispatcher            
     */
    public function __construct(DatabaseStorageInterface $storage, array $ids = [], bool $buffering = true, EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($storage, self::getModelBinderId($ids), $buffering, $eventDispatcher);
    }

    public static function getModelBinderId(array $ids): array
    {
        $idFields = [];
        $id = [];
        
        foreach ($ids as $idKey => $idValue) {
            // If id is bindable model object then extract id
            if ($idValue instanceof BindableModelInterface) {
                $idFields[spl_object_hash($idValue)] = $idValue->getBindingFieldName();
            } else {
                // Explicitly set id
                $id[$idKey] = $idValue;
            }
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

        // Extract ids from BindableModelInterface
        foreach ($ids as $idValue) {
            if ($idValue instanceof BindableModelInterface) {
                $modelId = $idValue->getId();
                if (is_null($modelId)) {
                    throw new \InvalidArgumentException('Cannot use Model without id as one of ModelBinder arguments');
                }
                $id[$idFields[spl_object_hash($idValue)]] = $modelId;
            }
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
            $this->synchronize();
        }
        return is_null($key) ? $this->id : ($this->id[$key] ?? null);
    }
    /**
     * Execute actions that syncs storage with model data
     *
     * @return bool
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