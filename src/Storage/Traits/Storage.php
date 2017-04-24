<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Storage\Traits;

use Minwork\Storage\Interfaces\StorageInterface;

/**
 * Basic getter and setter for storage inside object
 * 
 * @author Christopher Kalkhoff
 *        
 */
trait Storage {

    /**
     * Storage object
     * 
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Get storage object
     * 
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Set storage object
     * 
     * @param StorageInterface $storage            
     * @return self
     */
    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;
        return $this;
    }
}