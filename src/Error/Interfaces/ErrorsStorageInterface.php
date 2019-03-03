<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Interfaces;

/**
 * Interface for errors storage
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ErrorsStorageInterface
{

    /**
     * If storage contains any errors
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Get list of error objects
     *
     * @param mixed $config            
     * @return ErrorInterface[]
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
     * @param ErrorsStorageInterface $storage
     * @return self
     */
    public function merge(ErrorsStorageInterface $storage): self;

    /**
     * Clear errors list
     *
     * @return self
     */
    public function clearErrors(): self;
}