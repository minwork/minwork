<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Interfaces;

use Minwork\Error\Interfaces\ErrorsStorageInterface;

/**
 * Interface for validator object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ValidatorInterface
{

    /**
     * Set object which is validated
     * 
     * @param mixed $object            
     * @return self
     */
    public function setObject($object): self;

    /**
     * Get validated object
     */
    public function getObject();

    /**
     * If validation was successful
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Validate supplied data
     *
     * @param mixed $data            
     * @return self
     */
    public function validate($data): self;

    /**
     * Get errors storage
     *
     * @return ErrorsStorageInterface
     */
    public function getErrors(): ErrorsStorageInterface;
}