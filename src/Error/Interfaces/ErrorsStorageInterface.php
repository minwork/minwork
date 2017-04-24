<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Interfaces;

interface ErrorsStorageInterface
{

    /**
     * If storage contains any errors
     * 
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Get array of printable errors
     * 
     * @param array|null $config            
     * @return array
     */
    public function getErrors($config = null): array;

    /**
     * Add error object to storage
     * 
     * @param ErrorInterface $error            
     * @return self
     */
    public function addError(ErrorInterface $error): self;

    /**
     * Merge error storage with another
     * 
     * @param ErrorsStorageInterface $error            
     * @return self
     */
    public function merge(ErrorsStorageInterface $error): self;

    /**
     * Clear errors array
     * 
     * @return self
     */
    public function clearErrors(): self;
}