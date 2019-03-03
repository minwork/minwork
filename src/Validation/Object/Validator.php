<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Object;

use Minwork\Error\Interfaces\ErrorsStorageContainerInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator as ValidatorTrait;
use Minwork\Validation\Utility\Field;

/**
 * Basic implementation of validator interface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Validator implements ValidatorInterface, ErrorsStorageContainerInterface
{
    use ValidatorTrait, Errors;

    /**
     * Validation config which is a list of objects implementing ValidatorInterface
     *
     * @var ValidatorInterface[]
     */
    protected $validators = [];

    /**
     * Initialize validator with array of objects implementing ValidatorInterface
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct(ValidatorInterface ...$validators)
    {
        $this->addValidator(...$validators);
    }

    /**
     * Set validator config which is list of ValidatorInterface objects
     *
     * @param ValidatorInterface[] $validator
     * @return self
     */
    
    public function addValidator(ValidatorInterface ...$validator): self
    {
        foreach ($validator as $v) {
            $this->validators[] = $v;
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::validate()
     * @throws \Exception
     */
    public function validate(...$data): ValidatorInterface
    {
        $this->clearErrors();
        
        if (empty($data)) {
            $this->addError('No data provided');
            $this->valid = false;
            return $this;
        }

        $this->valid = true;
        
        foreach ($this->validators as $validator) {
            // Set context
            if ($this->hasContext()) {
                $validator->setContext($this->getContext());
            }
            
            // Handle fields
            if ($validator instanceof Field) {
                $fieldArguments = [];
                // Extract field data from $data
                foreach ($data as $argument) {
                    if (is_array($argument) && array_key_exists($validator->getName(), $argument)) {
                        $fieldArguments[] = $argument[$validator->getName()];
                    }
                }
                
                // If field is mandatory but doesnt have any data supplied, then trigger it's error
                if ($this->hasEmptyArguments($fieldArguments)) {
                    if ($validator->isMandatory()) {
                        $validator->addError($validator->getName(), $validator->getError())->setValid(false);
                    }
                } else {
                    $validator->validate(...$fieldArguments);
                }
            } else {
                $validator->validate(...$data);
            }
            
            if (! $validator->isValid()) {
                $this->setValid(false);
                $this->getErrorsStorage()->merge($validator->getErrorsStorage());
                if (method_exists($validator, 'hasCriticalError') && $validator->hasCriticalError()) {
                    break;
                }
            }
        }
        
        $this->setValid($this->isValid() && ! $this->hasErrors());
        
        return $this;
    }
    
    protected function hasEmptyArguments(array $arguments): bool
    {
        foreach ($arguments as $arg) {
            if (is_numeric($arg) || (is_array($arg) && ! $this->hasEmptyArguments($arg)) || ! empty($arg)) {
                return false;
            }
        }
        
        return true;
    }
}