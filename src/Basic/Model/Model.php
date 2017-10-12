<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Model;

use Minwork\Error\Traits\Errors;
use Minwork\Operation\Basic\Read;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Database\Utility\Query;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Event\Traits\Events;
use Minwork\Operation\Traits\Operations;
use Minwork\Helper\ArrayHelper;
use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Basic\Interfaces\ModelInterface;
use Minwork\Storage\Traits\Storage;
use Minwork\Operation\Interfaces\ObjectOperationInterface;
use Minwork\Basic\Interfaces\BindableModelInterface;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Basic\Traits\Debugger;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Helper\Formatter;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Event\Traits\Connector;

/**
 * Basic implementation of ModelInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Model implements ModelInterface, ObjectOperationInterface, BindableModelInterface, EventDispatcherContainerInterface
{
    use Connector, Errors, Events, Debugger, Operations, Storage {
      getStorage as getStorageTrait;
      setStorage as setStorageTrait;
    }
    
    // Empty uninitialized model
    const STATE_EMPTY = "EMPTY";
    
    // Indicates need of creating corresponding record in database storage
    const STATE_CREATE = "CREATE";
    
    // Indicates need of updating corresponding record in database storage
    const STATE_UPDATE = "UPDATE";
    
    // Indicates no need for any operations - set after executing actions (creating or updating)
    const STATE_NOP = "NOP";

    /**
     * Model identifier which can be either single value or an array in form of [{id_name} => {id_value}, ...]
     *
     * @var int|string|array|null
     */
    protected $id = null;

    /**
     * Contain associative array of database fields and their corresponding values
     *
     * @var array|null
     */
    protected $data;

    /**
     * List of key names that were changed in $data
     *
     * @var array
     */
    protected $changedData;

    /**
     * If record of model with specified id exist in database storage
     *
     * @var boolean|null
     */
    protected $exists;

    /**
     * Model state determining next action which should be made to keep it in synch with database storage
     *
     * @var string
     */
    protected $state;

    /**
     * Indicates if model should synchronize state with storage immediately or whenever neccessary
     *
     * @see \Minwork\Basic\Model\Model::setBuffering()
     * @var bool
     */
    protected $buffering;

    /**
     *
     * @param DatabaseStorageInterface $storage            
     * @param int|string|array|null $id            
     * @param bool $buffering            
     * @param EventDispatcherInterface $eventDispatcher            
     */
    public function __construct(DatabaseStorageInterface $storage, $id = null, bool $buffering = true, EventDispatcherInterface $eventDispatcher = null): void
    {
        $this->reset()
            ->setStorage($storage)
            ->setId($id)
            ->setEventDispatcher($eventDispatcher ?? new EventDispatcher())
            ->setBuffering($buffering)
            ->connect();
    }

    /**
     * Clone model and all objects set through dependency injection
     * Reset event dispatcher and reconnect all events to newly created model
     */
    public function __clone(): void
    {
        $this->setStorage(clone $this->getStorage())
            ->setEventDispatcher((clone $this->getEventDispatcher())->reset())
            ->connect();
    }

    /**
     * Executes storage actions if necessary
     */
    public function __destruct(): void
    {
        if ($this->requireAction()) {
            $this->executeActions();
        }
    }

    /**
     * Reset to initial properties values with exception of storage and event dispatcher
     *
     * @return self
     */
    public function reset(): self
    {
        $this->id = null;
        $this->data = null;
        $this->changedData = [];
        $this->exists = null;
        $this->state = self::STATE_EMPTY;
        return $this;
    }

    /**
     * Set if model operations on data which result in storage changes should be buffered (executed whenever neccessary) or take effect immidiately
     *
     * @param bool $buffering            
     * @return self
     */
    public function setBuffering(bool $buffering): self
    {
        $this->buffering = $buffering;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\BindableModelInterface::getBindingFieldName()
     */
    public function getBindingFieldName(): string
    {
        $idField = $this->getStorage()->getPkField();
        if (! is_string($idField)) {
            throw new \Exception('Cannot bind model with multiple id fields');
        }
        if ($this->getStorage() instanceof TableInterface) {
            $name = $this->getStorage()->getName(false);
        } else {
            $name = mb_strtolower(get_class($this));
        }
        return "{$name}_{$idField}";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @return \Minwork\Storage\Interfaces\DatabaseStorageInterface
     */
    public function getStorage(): DatabaseStorageInterface
    {
        return $this->getStorageTrait();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::setStorage($storage)
     */
    public function setStorage(DatabaseStorageInterface $storage): ModelInterface
    {
        return $this->setStorageTrait($storage);
    }

    /**
     * Set internal model state
     *
     * @param string $state            
     * @return self
     */
    protected function setState(string $state): self
    {
        if ($this->state !== $state) {
            $this->state = $state;
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::getId()
     */
    public function getId()
    {
        if (is_null($this->id) && $this->state === self::STATE_CREATE && ! empty($this->data)) {
            $this->executeActions();
        }
        return $this->id;
    }

    /**
     * Get id normalized to array with column name as key and id as value like [{id_name} => {id_value}, ...]
     *
     * @return array
     */
    public function getNormalizedId(): array
    {
        $id = $this->getId();
        $idFields = ArrayHelper::forceArray($this->getStorage()->getPkField());
        if (is_array($id)) {
            if (! ArrayHelper::isAssoc($id, true)) {
                // If id isnt assoc but has same number of elements as id columns treat it as values to those columns
                if (count($id) === count($idFields)) {
                    return array_combine($idFields, $id);
                } else {
                    $this->debug('Invalid id value: ' . Formatter::toString($id));
                    return [];
                }
            } else {
                return $id;
            }
        } elseif (is_string($id) || is_numeric($id)) {
            $pkField = $this->getStorage()->getPkField();
            if (is_array($pkField)) {
                $this->debug('Model id value is singular while storage have multiple primary key fields: ' . Formatter::toString($pkField));
                $pkField = reset($pkField);
            }
            return [
                $pkField => $id
            ];
        }
        return [];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::setId($id)
     */
    public function setId($id): ModelInterface
    {
        if (is_null($id)) {
            $this->id = $id;
            $this->exists = false;
        } else {
            if (is_array($id)) {
                $this->id = ArrayHelper::isAssoc($id, true) ? ArrayHelper::filterByKeys($id, ArrayHelper::forceArray($this->getStorage()->getPkField())) : (count($id) === 1 ? reset($id) : $id);
            } else {
                $this->id = $id;
            }
            $this->exists = null;
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::exists()
     */
    public function exists(): bool
    {
        if (is_null($this->getId())) {
            $this->exists = false;
        } elseif (is_null($this->exists)) {
            $this->exists = $this->getStorage()->isset(new Query($this->getQueryConditionsWithId()));
        }
        
        return $this->exists;
    }

    /**
     * If model require actions on storage to keep it in sync
     *
     * @return bool
     */
    protected function requireAction(): bool
    {
        return in_array($this->state, [
            self::STATE_CREATE,
            self::STATE_UPDATE
        ]);
    }

    /**
     * Execute actions that sync storage with model data
     *
     * @return bool
     */
    public function executeActions(): bool
    {
        if ($this->requireAction()) {
            $state = $this->state;
            $this->state = self::STATE_NOP;
            switch ($state) {
                case self::STATE_CREATE:
                    $insertData = $this->getChangedData();
                    // Get id without executing same method again
                    $idArray = $this->getNormalizedId();
                    if (empty($idArray)) {
                        $this->getStorage()->set(new Query([], array_keys($insertData)), array_values($insertData));
                        $id = $this->getStorage()
                            ->getDatabase()
                            ->getLastInsertId();
                        if ($id !== false) {
                            $this->setId($id);
                            $this->exists = true;
                            return true;
                        }
                        $this->debug('No id received from storage');
                        return false;
                    } else {
                        $insertData = array_merge($insertData, $idArray);
                        $this->getStorage()->set(new Query([], array_keys($insertData)), array_values($insertData));
                        return true;
                    }
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

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::execute($operation, $arguments, $validator)
     */
    public function execute(OperationInterface $operation, array $arguments = [], ValidatorInterface $validator = null)
    {
        if (! is_null($validator) && ! $validator->setContext($this)
            ->validate(count($arguments) === 1 ? reset($arguments) : $arguments)
            ->isValid()) {
            $this->getErrors()->merge($validator->getErrors());
            return false;
        }
        $result = $this->executeOperation($operation->setEventDispatcher($this->getEventDispatcher()), $arguments);
        
        if (! $this->buffering) {
            $this->executeActions();
        }
        
        return $result;
    }

    /**
     * Get data that changed in compare to inital state or after executing actions
     *
     * @return array
     */
    protected function getChangedData(): array
    {
        $changed = [];
        
        foreach ($this->changedData as $key) {
            $changed[$key] = $this->data[$key];
        }
        return $changed;
    }

    /**
     * Compute changed data based on actual model data in compare to supplied array
     *
     * @param array $data            
     * @return self
     */
    protected function markAsChanged(array $data): self
    {
        foreach (array_keys($data) as $key) {
            array_push($this->changedData, $key);
        }
        $this->changedData = array_unique($this->changedData);
        return $this;
    }

    /**
     * Append id condition to array supplied into storage Query
     *
     * @param array $conditions            
     * @return array
     */
    protected function getQueryConditionsWithId(array $conditions = []): array
    {
        if (is_null($this->getId())) {
            throw new \Exception('Cannot append id to conditions when no id is set');
        }
        
        $id = $this->getNormalizedId();
        // If id fields doesnt exists in conditions array
        if (count(array_intersect_key($conditions, array_flip(array_keys($id)))) === 0) {
            $conditions = array_merge($conditions, $id);
        }
        return $conditions;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::getData($filter)
     * @param array|string|null $filter
     *            If filter is string then single data element is returned otherwise filtered data array
     */
    public function getData($filter = null)
    {
        if (is_null($this->getId())) {
            $this->debug('Trying to get data on model without id');
            if (empty($this->data)) {
                return is_array($filter) ? [] : null;
            }
        }
        
        $fields = $this->getStorage()->getFields();
        $getData = true;
        
        if (is_null($this->data)) {
            $this->data = [];
        }
        
        if (is_null($filter)) {
            // If we have partial data and need full
            if (! empty($this->data) && count($this->data) < count($fields)) {
                $filterArray = array_diff($fields, array_keys($this->data));
            } else {
                $filterArray = $fields;
            }
        } else {
            $filterArray = ArrayHelper::forceArray($filter);
        }
        
        // Check if we have needed data loaded
        $neededData = array_diff($filterArray, array_keys($this->data));
        if (count($neededData) > 0) {
            $filterArray = $neededData;
        } else {
            // If we have needed data skip storage get
            $getData = false;
        }
        
        // If model doesn't contain all needed data then get it from storage by executing read operation
        if ($getData) {
            $this->execute(new Read(), [$filterArray]);
        }
        
        return ! is_null($filter) && (is_string($filter) || is_int($filter)) ? $this->data[$filter] : (is_null($filter) ? $this->data : array_intersect_key($this->data, array_flip($filter)));
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::setData($data, $merge)
     */
    public function setData(array $data, bool $merge = true): ModelInterface
    {
        if ($merge && ! is_null($this->data)) {
            foreach ($data as $key => $value) {
                if (! isset($this->data[$key]) || $this->data[$key] !== $value) {
                    $this->data[$key] = $value;
                }
            }
        } else {
            // If we doesn't merge data then search for id column inside data
            if (($count = count($ids = ArrayHelper::filterByKeys($data, ArrayHelper::forceArray($this->getStorage()->getPkField())))) > 0) {
                $this->setId($count === 1 ? reset($ids) : $ids);
            }
            $this->data = $data;
        }
        if ($this->state === self::STATE_EMPTY && ! empty($this->data)) {
            $this->setState(self::STATE_NOP);
        }
        return $this;
    }

    /**
     * Create operation
     *
     * @param array $data            
     * @return bool
     */
    public function create(array $data = []): bool
    {
        $data = ArrayHelper::filterByKeys($data, $this->getStorage()->getFields());
        $this->setState(self::STATE_CREATE)
            ->setData($data, false)
            ->markAsChanged($data);
        return true;
    }

    /**
     * Read operation (get data from storage then set it to model)
     *
     * @param array $filter   
     * @return self
     */
    public function read(array $filter = []): self
    {
        $data = $this->getStorage()->get(new Query($this->getQueryConditionsWithId(), $filter, 1));
        // If data from storage is same as current data and current data is in changed list then remove it from that list
        $toRemove = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data) && (string) $this->data[$key] === (string) $value && in_array($key, $this->changedData)) {
                $toRemove[] = $key;
            }
        }
        $this->changedData = array_diff($this->changedData, $toRemove);
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Update operation
     *
     * @param array $data            
     * @return bool
     */
    public function update(array $data): bool
    {
        $data = ArrayHelper::filterByKeys($data, $this->getStorage()->getFields());
        $this->setState(self::STATE_UPDATE)
            ->setData($data)
            ->markAsChanged($data);
        return true;
    }

    /**
     * Delete operation
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->exists()) {
            $this->getStorage()->unset(new Query($this->getQueryConditionsWithId()));
        }
        $this->reset();
        return true;
    }
}