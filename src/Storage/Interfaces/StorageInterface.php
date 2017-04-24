<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Storage\Interfaces;

/**
 * Basic interface for storages
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface StorageInterface
{

    /**
     * Get value of stored object
     * 
     * @param mixed $key
     *            If null this should return all storage elements unless $key is set to default value inside a method
     */
    public function get($key);

    /**
     * Set value of selected element in storage
     *
     * @param mixed $key            
     * @param mixed $value            
     * @return self
     */
    public function set($key, $value): self;

    /**
     * Check if value exists for given key
     * 
     * @param mixed $key            
     * @return bool
     */
    public function isset($key): bool;

    /**
     * Remove from storage
     * 
     * @param mixed $key
     *            If null every storage element should be removed
     * @return self
     */
    public function unset($key): self;

    /**
     * Count matching elements in storage
     * 
     * @param mixed $key
     *            If null this should return count of all storage elements
     * @return int
     */
    public function count($key): int;
}