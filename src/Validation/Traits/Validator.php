<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Traits;

use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Validation\Interfaces\ValidatorInterface;

trait Validator {
    /**
     * If validation was successful
     * 
     * @var bool
     */
    protected $valid = true;

    /**
     * Validation context (usually object where validation is called)
     *
     * @var mixed
     */
    protected $context = null;

    /**
     * Operation object (usually operation that will be executed after validation)
     *
     * @var OperationInterface
     */
    protected $operation;

    /**
     * If current validator has context
     * 
     * @return bool
     */
    public function hasContext(): bool
    {
        return ! is_null($this->context);
    }

    /**
     * Get validation context
     * 
     * @return mixed|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set optional context for validation so any sub-validators can access it (i.e. model during form data validation)
     *
     * @param mixed $context
     * @return ValidatorInterface
     */
    public function setContext($context): ValidatorInterface
    {
        $this->context = $context;
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * Optionally set operation object to access during validation
     *
     * @param OperationInterface $operation
     * @return ValidatorInterface
     */
    public function setOperation(OperationInterface $operation): ValidatorInterface
    {
        $this->operation = $operation;
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * If validator has set operation
     * @return bool
     */
    public function hasOperation(): bool
    {
        return !is_null($this->operation);
    }

    /**
     * Get optionally set operation object
     *
     * @return OperationInterface
     */
    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    /**
     * If validation was successful
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return boolval($this->valid);
    }

    /**
     * Arbitrary set if validator validation was successful
     *
     * @param bool $valid
     * @return ValidatorInterface
     */
    public function setValid(bool $valid): ValidatorInterface
    {
        $this->valid = $valid;
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }
}