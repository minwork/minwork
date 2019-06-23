<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Model;

use Exception;
use Minwork\Basic\Interfaces\BindableModelInterface;
use Minwork\Basic\Interfaces\ModelInterface;
use Minwork\Basic\Traits\Debugger;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Query;
use Minwork\Error\Interfaces\ErrorsStorageContainerInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Event\Traits\Connector;
use Minwork\Event\Traits\Events;
use Minwork\Helper\Arr;
use Minwork\Helper\Formatter;
use Minwork\Operation\Basic\Read;
use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Operation\Object\OperationEvent;
use Minwork\Operation\Traits\Operations;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Storage\Interfaces\StorageInterface;
use Minwork\Storage\Traits\Storage;
use Minwork\Validation\Interfaces\ValidatorInterface;

/**
 * Basic implementation of ModelInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Model implements ModelInterface, BindableModelInterface, ErrorsStorageContainerInterface
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
     * @param DatabaseStorageInterface|TableInterface|StorageInterface $storage
     * @param int|string|array|null $id            
     * @param bool $buffering            
     * @param EventDispatcherInterface $eventDispatcher            
     */
    public function __construct(DatabaseStorageInterface $storage, $id = null, bool $buffering = true, EventDispatcherInterface $eventDispatcher = null)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->reset()
            ->setBuffering($buffering)
            ->setStorage($storage)
            ->setId($id)
            ->setEventDispatcher($eventDispatcher ?? new EventDispatcher())
            ->connect();
    }

    /**
     * Clone model and all objects set through dependency injection
     * Reset event dispatcher and reconnect all events to newly created model
     */
    public function __clone()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setStorage(clone $this->getStorage())
            ->setEventDispatcher((clone $this->getEventDispatcher())->reset())
            ->connect();
    }

    /**
     * Executes storage actions if necessary
     */
    public function __destruct()
    {
        if ($this->requireAction()) {
            $this->synchronize();
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
     * @throws Exception
     * @see \Minwork\Basic\Interfaces\BindableModelInterface::getBindingFieldName()
     */
    public function getBindingFieldName(): string
    {
        $idField = $this->getStorage()->getPkField();
        if (! is_string($idField)) {
            throw new Exception('Cannot bind model with multiple id fields');
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
     * @return DatabaseStorageInterface|TableInterface|StorageInterface
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
        if ($this->state !== $state && ! ($state === self::STATE_UPDATE && $this->state === self::STATE_CREATE)) {
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
            $this->synchronize();
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
        $idFields = Arr::forceArray($this->getStorage()->getPkField());
        if (is_array($id)) {
            if (! Arr::isAssoc($id, true)) {
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
        $storage = $this->getStorage();
        $idField = $storage->getPkField();

        if (is_null($id)) {
            $this->id = $id;
            $this->exists = false;
        } else {
            if (is_array($id)) {
                if (is_string($idField) && (($keyExists = array_key_exists($idField, $id)) || count($id) === 1)) {
                    $this->id = $keyExists ? $id[$idField] : reset($id);
                } else {
                    $idFields = Arr::forceArray($idField);
                    $this->id = Arr::isAssoc($id, true) ? Arr::orderByKeys(Arr::filterByKeys($id, $idFields), $idFields) : $id;
                }
            } else {
                $this->id = $id;
            }
            $this->exists = null;
        }

        // If build-in table handler then automatically format id
        if (!is_null($this->id) && $storage instanceof TableInterface) {
            // Format id field according to column config
            if (is_array($this->id) && Arr::isAssoc($this->id, true)) {
                // If id is assoc array then just format
                $this->id = $storage->format($this->id);
            } elseif (!is_array($this->id) && is_string($idField)) {
                $this->id = $storage->format([ $idField => $this->id ])[$idField];
            }
            // Don't format otherwise cause we don't know exact mapping of id fields to current id
        }

        return $this;
    }

    /**
     * Load model using data array which can contain id
     *
     * @param array $data
     * @return ModelInterface
     */
    public function initFromData(array $data): ModelInterface
    {
        // Format data according to database columns
        $data = $this->getStorage()->format($data);
        // If we have id in data then set it
        $ids = Arr::filterByKeys($data, Arr::forceArray($this->getStorage()->getPkField()));
        if (! empty($ids)) {
            $this->setId($ids);
            $data = array_diff_key($data, $ids);
        }
        
        // Set rest of data and emulate read operation
        $this->getEventDispatcher()->dispatch(new OperationEvent(Read::EVENT_BEFORE));
        $this->setData($data, false);
        $this->getEventDispatcher()->dispatch(new OperationEvent(Read::EVENT_AFTER));
        
        return $this;
    }
    
    /**
     * Try to load model data using specified query.
     * Return if load was successful
     * 
     * @param Query $query
     * @return bool
     */
    public function initFromStorage(Query $query): bool
    {
        if (is_null($query->getLimit())) {
            $query->setLimit(1);
        }
        
        $data = $this->getStorage()->get($query);
        if (! empty($data) && ! Arr::isArrayOfArrays($data)) {
            $this->initFromData($data);
            return true;
        }
        
        return false;
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
            try {
                $this->exists = $this->getStorage()->isset(new Query($this->getQueryConditionsWithId()));
            } catch (Exception $e) {
                return false;
            }
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
    public function synchronize(): bool
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
                        try {
                            $this->getStorage()->set(new Query($this->getQueryConditionsWithId()), $updateData);
                        } catch (Exception $e) {
                            return false;
                        }
                    }
                    return true;
                    break;
            }
        }
        
        return false;
    }

    /**
     * Execute supplied operation
     *
     * @param OperationInterface $operation Operation object
     * @param mixed ...$arguments Operation arguments
     * @return mixed
     */
    public function execute(OperationInterface $operation, ...$arguments)
    {
        if ($operation instanceof EventDispatcherContainerInterface) {
            $operation->setEventDispatcher($this->getEventDispatcher());
        }

        $result = $this->executeOperation($operation, ...$arguments);
        
        return $result;
    }

    /**
     * Validate using supplied validator then execute operation if validation was successful
     * @param OperationInterface $operation
     *            Operation object
     * @param ValidatorInterface $validator
     *            Validator object
     * @param mixed ...$arguments
     *            Operation arguments
     * @return bool|mixed
     */
    public function validateThenExecute(OperationInterface $operation, ValidatorInterface $validator, ...$arguments)
    {
        if (! $validator->setContext($this)
            ->validate(...$arguments)
            ->isValid()) {
                $this->getErrorsStorage()->merge($validator->getErrorsStorage());
                return false;
        }
        
        return $this->execute($operation, ...$arguments);
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
     * @throws Exception
     */
    protected function getQueryConditionsWithId(array $conditions = []): array
    {
        if (is_null($this->getId())) {
            throw new Exception('Cannot append id to conditions when no id is set');
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
            $filterArray = Arr::forceArray($filter);
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
            $readFilter = array_intersect($fields, $filterArray);
            
            if (! empty($readFilter)) {
                $this->execute(new Read(), $readFilter);
            }
        }
        
        return ! is_null($filter) && (is_string($filter) || is_int($filter)) ? ($this->data[$filter] ?? null) : (is_null($filter) ? $this->data : Arr::filterByKeys($this->data, $filter));
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
                if (! array_key_exists($key, $this->data) || $this->data[$key] !== $value) {
                    $this->data[$key] = $value;
                }
            }
        } else {
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
        // When creating search for ids
        $ids = Arr::filterByKeys($data, Arr::forceArray($this->getStorage()->getPkField()));
        $data = Arr::filterByKeys($data, $this->getStorage()->getFields());
        
        if (! empty($ids)) {
            $this->setId($ids);
        }

        $this->setState(self::STATE_CREATE)
            ->markAsChanged($data)
            ->setData($data, false);

        if (! $this->buffering) {
            $this->synchronize();
        }
        
        return true;
    }

    /**
     * Read operation (get data from storage then set it to model)
     *
     * @param array $filter
     * @return self
     * @throws Exception
     */
    public function read(array $filter = []): self
    {
        $data = $this->getStorage()->get(new Query($this->getQueryConditionsWithId(), $filter, 1));
        // If data from storage is same as current data and current data is in changed list then remove it from that list
        $toRemove = [];
        
        // Initialize data array if necessary
        if (is_null($this->data)) {
            $this->data = [];
        }
        
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
        $data = Arr::filterByKeys($data, $this->getStorage()->getFields());
        
        if (! empty($data)) {
            $this->setState(self::STATE_UPDATE)
            ->markAsChanged($data)
            ->setData($data);

            if (! $this->buffering) {
                $this->synchronize();
            }
        }
            
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
            try {
                $this->getStorage()->unset(new Query($this->getQueryConditionsWithId()));
            } catch (Exception $e) {
                return false;
            }
        }
        
        $this->reset();
        return true;
    }
}