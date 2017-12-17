<?php
namespace Minwork\Validation\Utility;

use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator;
use Minwork\Error\Basic\ErrorGlobal;

/**
 *
 * @author Christopher Kalkhoff
 *        
 */
class Field implements ValidatorInterface
{
    use Validator;

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
     * @var string
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
     * @param string $error
     *            Global error when field is mandatory but didn't found key corresponding to its name during validation on $data array
     */
    public function __construct(string $name, array $rules = [], bool $mandatory = true, string $error = '')
    {
        $this->name = $name;
        $this->mandatory = $mandatory;
        $this->error = $error === '' ? "Field {$name} is mandatory" : $error;
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
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setRules(array $rules): self
    {
        foreach ($rules as $rule) {
            if (! is_object($rule) || ! $rule instanceof Rule) {
                throw new \InvalidArgumentException('Field rule must be an Rule object');
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
     * @return string
     */
    public function getError(): string
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
                foreach ($rule->getErrors()->getErrors(ErrorGlobal::TYPE) as $error) {
                    $this->addError($this->getName(), $error->getMessage());
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