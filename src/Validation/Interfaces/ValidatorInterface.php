<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Interfaces;

use Minwork\Error\Interfaces\ErrorsStorageInterface;
use Minwork\Operation\Interfaces\OperationInterface;

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
     * Optionally set operation object to access during validation
     *
     * @param OperationInterface $operation
     * @return ValidatorInterface
     */
    public function setOperation(OperationInterface $operation): self;

    /**
     * If validator has set operation
     * @return bool
     */
    public function hasOperation(): bool;

    /**
     * Get optionally set operation object
     *
     * @return OperationInterface|null
     */
    public function getOperation(): ?OperationInterface;

    /**
     * Validate supplied data
     *
     * @param mixed $data            
     * @return self
     */
    public function validate(...$data): self;
    
    /**
     * If validation was successful
     *
     * @return bool
     */
    public function isValid(): bool;


    /**
     * Arbitrary set if validator validation was successful
     *
     * @param bool $valid
     * @return self
     */
    public function setValid(bool $valid): self;

    /**
     * Get errors storage
     *
     * @return ErrorsStorageInterface
     */
    public function getErrorsStorage(): ErrorsStorageInterface;
}