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
     */
    public function get($key);

    /**
     * Set value of an element selected by $key
     *
     * @param mixed $key            
     * @param mixed $value            
     * @return self
     */
    public function set($key, $value): self;

    /**
     * Check if value exists for supplied $key
     *
     * @param mixed $key            
     * @return bool
     */
    public function isset($key): bool;

    /**
     * Remove from storage
     *
     * @param mixed $key            
     * @return self
     */
    public function unset($key): self;

    /**
     * Count elements matching $key
     *
     * @param mixed $key            
     * @return int
     */
    public function count($key): int;
}