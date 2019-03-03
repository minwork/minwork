<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Traits;

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
    protected $context;

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
     * @throws \Exception
     * @return mixed
     */
    public function getContext()
    {
        if (! $this->hasContext()) {
            throw new \Exception('No context is set');
        }
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
        return $this;
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
     * Set if validation was successful
     * 
     * @param bool $valid
     * @return self
     */
    public function setValid(bool $valid): self
    {
        $this->valid = $valid;
        
        return $this;
    }
}