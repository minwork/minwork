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
     * Set optional context for validation so any sub-validators can access it (like model object during form data validation)
     *
     * @param mixed $context            
     * @return self
     */
    public function setContext($context): self;

    /**
     * If current validator has context
     * @return bool
     */
    public function hasContext(): bool;
    
    /**
     * Get validation context
     * 
     * @return mixed
     */
    public function getContext();

    /**
     * Validate supplied data
     *
     * @param mixed $data            
     * @return self
     */
    public function validate($data): self;
    
    /**
     * If validation was successful
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get errors storage
     *
     * @return ErrorsStorageInterface
     */
    public function getErrors(): ErrorsStorageInterface;
}