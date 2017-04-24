<?php
namespace Minwork\Validation\Utility;

use Minwork\Validation\Interfaces\FieldInterface;
use Minwork\Validation\Interfaces\RuleInterface;

class Field implements FieldInterface
{

    /**
     * Field name
     * 
     * @var string
     */
    protected $name;

    /**
     * Field rules
     * 
     * @see Field::setRules()
     * @var RuleInterface[]
     */
    protected $rules;

    /**
     * Is field mandatory
     * 
     * @var bool
     */
    protected $mandatory;

    /**
     * String representation of error
     * 
     * @see FieldInterface::isMandatory()
     * @var string
     */
    protected $error;

    public function __construct(string $name, array $rules = [], bool $mandatory = true, string $error = '')
    {
        $this->name = $name;
        $this->setRules($rules);
        $this->mandatory = $mandatory;
        $this->error = $error;
    }

    /**
     * Set field rules
     *
     * @param RuleInterface[] $rules            
     * @throws \InvalidArgumentException
     * @return \Minwork\Validation\Utility\Field
     */
    public function setRules(array $rules)
    {
        foreach ($rules as $rule) {
            if (! $rule instanceof RuleInterface) {
                throw new \InvalidArgumentException('Rule must be object implementing RuleInterface');
            }
        }
        $this->rules = $rules;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getError()
     */
    public function getError(): string
    {
        // If error is empty produce default output
        if (empty($this->error)) {
            $this->error = "Field {$this->getName()} is mandatory";
        }
        return $this->error;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::isMandatory()
     */
    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getRules()
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}