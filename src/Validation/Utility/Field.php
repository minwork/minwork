<?php
namespace Minwork\Validation\Utility;

use Exception;
use InvalidArgumentException;
use Minwork\Error\Interfaces\ErrorInterface;
use Minwork\Error\Interfaces\ErrorsStorageContainerInterface;
use Minwork\Error\Object\Error;
use Minwork\Error\Traits\Errors;
use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator;

/**
 *
 * @author Christopher Kalkhoff
 *        
 */
class Field implements ValidatorInterface, ErrorsStorageContainerInterface
{
    use Validator, Errors;

    /**
     * Field name
     *
     * @var string
     */
    protected $name;

    /**
     * Field rules
     *
     * @var Rule[]
     */
    protected $rules;

    /**
     * If field is mandatory
     *
     * @var bool
     */
    protected $mandatory;

    /**
     * Global error when field is mandatory but didn't found key corresponding to its name during validation on $data array
     *
     * @var ErrorInterface
     */
    protected $error;

    /**
     * If field has critical error and should immidietely stop further validation
     * This property is propagated from Rule objects
     *
     * @var bool
     */
    protected $hasCriticalError = false;

    /**
     *
     * @param string $name
     *            Field form name
     * @param Rule[] $rules
     *            Set of rules for validating this field
     * @param bool $mandatory
     *            If this field is mandatory and must be present in $data array in validate method
     * @param ErrorInterface|null $error
     *            Global error when field is mandatory but didn't found key corresponding to its name during validation on $data array
     */
    public function __construct(string $name, array $rules = [], bool $mandatory = true, ?ErrorInterface $error = null)
    {
        $this->name = $name;
        $this->mandatory = $mandatory;
        $this->error = $error ?? new Error("Field {$name} is mandatory");
        $this->setRules($rules);
    }
    
    /**
     * Get field string representation
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Set field rules
     *
     * @param ValidatorInterface[] $rules            
     * @throws InvalidArgumentException
     * @return self
     */
    public function setRules(array $rules): self
    {
        foreach ($rules as $rule) {
            if (! is_object($rule) || ! $rule instanceof Rule) {
                throw new InvalidArgumentException('Field rule must be an Rule object');
            }
        }
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Get field name corresponding to input form name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * If this field is mandatory and should validate all the rules bond to it
     *
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
    
    /**
     * Get field error to display when it is mandatory but wasn't found
     * 
     * @return ErrorInterface
     */
    public function getError(): ErrorInterface
    {
        return $this->error;
    }

    /**
     * If field has critical error and should immidietely stop further validation
     * This property is propagated from Rule objects
     *
     * @return bool
     */
    public function hasCriticalError(): bool
    {
        return $this->hasCriticalError;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @param array $data
     *            Form data
     * @return ValidatorInterface
     * @throws Exception
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::validate()
     */
    public function validate(...$data): ValidatorInterface
    {
        $this->clearErrors();
        $this->valid = true;
        
        // If field is not mandatory and no data was supplied
        if (! $this->isMandatory() && count(array_filter($data, function ($arg) { return $arg !== ''; })) === 0) {
            return $this;
        }
        
        foreach ($this->rules as $rule) {
            // Set rule context
            if ($this->hasContext()) {
                $rule->setContext($this->getContext());
            }
            
            if (! $rule->validate(...$data)->isValid()) {
                $this->valid = false;
                // Add rule errors
                foreach ($rule->getErrorsStorage()->getErrors() as $error) {
                    $this->addError($error->setRef($this->getName()));
                }
                
                // If rule has critical error instantly break further errors validation
                if ($rule->hasCriticalError()) {
                    $this->hasCriticalError = true;
                    break;
                }
            }
        }
        
        return $this;
    }
}