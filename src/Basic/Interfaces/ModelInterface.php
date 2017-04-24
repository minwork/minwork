<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;

/**
 * Every model must implement that interface
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface ModelInterface
{

    /**
     * Run supplied operation with optional validator check which should be made before execution
     * 
     * @param OperationInterface $operation
     *            Operation object
     * @param array $arguments
     *            Operation arguments
     * @param ValidatorInterface $validator
     *            Validator object
     */
    public function execute(OperationInterface $operation, array $arguments = [], ValidatorInterface $validator = null);

    /**
     * Check if model with given id exists
     * 
     * @param bool $refresh
     *            Force fresh check
     * @return bool
     */
    public function exists(): bool;

    /**
     * Get model id which can be either single value or array like [{id_name} => {id_value}, ...]
     * 
     * @return string|int|array
     */
    public function getId();

    /**
     * Return model data
     * 
     * @param mixed $filter
     *            Which part of data should be returned (everything on null)
     * @return array
     */
    public function getData($filter = null);

    /**
     * Get storage object
     * 
     * @return DatabaseStorageInterface
     */
    public function getStorage(): DatabaseStorageInterface;

    /**
     * Set model id
     * 
     * @param string|int|array $id
     *            In case of array it should contain corresponding name and id like [{id_name} => {id_value}, ...]
     * @return self
     */
    public function setId($id): self;

    /**
     * Set model data
     * 
     * @param array $data            
     * @param bool $merge
     *            If data should merge to existing one or replace it
     * @return self
     */
    public function setData(array $data, bool $merge = true): self;

    /**
     * Set storage object
     * 
     * @param DatabaseStorageInterface $storage            
     * @return self
     */
    public function setStorage(DatabaseStorageInterface $storage): self;
}