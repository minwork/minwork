<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Operation\Interfaces\ObjectOperationInterface;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;

/**
 * Every model which represent single row in database must implement that interface
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ModelInterface extends ObjectOperationInterface, EventDispatcherContainerInterface
{
    /**
     * Check if model with given id exists
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Get model id which can be either single value or an array in form of [{id_name} => {id_value}, ...]
     *
     * @return string|int|array
     */
    public function getId();

    /**
     * Return model data which typically contain associative array of database fields values
     *
     * @param mixed $filter
     *            Which part of data should be returned (everything on null)
     * @return mixed
     */
    public function getData($filter = null);

    /**
     * Get database storage object
     *
     * @return DatabaseStorageInterface
     */
    public function getStorage(): DatabaseStorageInterface;

    /**
     * Set sdatabase torage object
     *
     * @param DatabaseStorageInterface $storage            
     * @return self
     */
    public function setStorage(DatabaseStorageInterface $storage): self;

    /**
     * Set model id
     *
     * @param string|int|array $id
     *            In case of an array it should contain corresponding name and id like [{id_name} => {id_value}, ...]
     * @return self
     */
    public function setId($id): self;

    /**
     * Set model data
     *
     * @see \Minwork\Basic\Interfaces\ModelInterface::getData()
     * @param array $data
     *            Typically it should have form like [{field_name} => {field_value}, ...]
     * @param bool $merge
     *            If data should be merged with existing one or replace it
     * @return self
     */
    public function setData(array $data, bool $merge = true): self;
}