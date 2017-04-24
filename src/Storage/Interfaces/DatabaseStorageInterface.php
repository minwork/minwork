<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Storage\Interfaces;

use Minwork\Database\Utility\Query;
use Minwork\Database\Interfaces\DatabaseInterface;

/**
 * Interface for database storage used in model
 *
 * @author Christopher Kalkhoff
 *        
 */
interface DatabaseStorageInterface extends StorageInterface
{
    /**
     * Get database object
     *
     * @return DatabaseInterface
     */
    public function getDatabase(): DatabaseInterface;

    /**
     * Get id field name or array of names
     * 
     * @return string|array
     */
    public function getPkField();

    /**
     * Get array of field names (excluding id)
     * 
     * @return array
     */
    public function getFields(): array;

/**
 *
 * @method get(Query $key)
 * @method set(Query $key, $value)
 * @method bool isset(Query $key)
 * @method unset(Query $key)
 * @method int count(Query $key)
 */
}