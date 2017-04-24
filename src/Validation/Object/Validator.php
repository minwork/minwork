<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Object;

use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Validation\Interfaces\RuleInterface;
use Minwork\Validation\Interfaces\FieldInterface;
use function foo\func;

/**
 * Basic implementation of validator interface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Validator implements ValidatorInterface
{
    
    use Errors;

    /**
     * If validation was successful
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Handle to validated object
     * 
     * @var mixed
     */
    protected $object;

    /**
     * Validation config
     *
     * @var array
     */
    protected $config;

    /**
     * Set validator config as array of rules or array of arrays of rules
     *
     * @param array $config
     *            Array of [{field} => {RuleObject}, ...] or [{field} => [{RuleObject}, ...], ...]
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::setObject()
     */
    public function setObject($object): ValidatorInterface
    {
        $this->object = $object;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::getObject()
     */
    public function getObject()
    {
        if (empty($this->object)) {
            throw new \Exception('No object is set');
        }
        return $this->object;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Validation\Interfaces\ValidatorInterface::isValid()
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Validation\Interfaces\ValidatorInterface::validate()
     */
    public function validate($data): ValidatorInterface
    {
        $this->clearErrors();
        
        if (empty($data)) {
            $this->addError('No data provided');
        } elseif (is_array($data)) {
            foreach ($this->config as $validator) {
                if (! is_object($validator)) {
                    throw new \InvalidArgumentException('Config elements must be objects');
                }
                
                if ($validator instanceof RuleInterface) {
                    /* @var $validator RuleInterface */
                    if (! $validator->setObject($this->getObject())->check($data)) {
                        $this->addError($validator->getError());
                        if ($validator->getImportance() == RuleInterface::IMPORTANCE_CRITICAL) {
                            break;
                        }
                    }
                } elseif ($validator instanceof FieldInterface) {
                    /* @var $object FieldInterface */
                    $name = $validator->getName();
                    
                    // If field is mandatory check if it exists in data
                    if (! array_key_exists($name, $data) && $validator->isMandatory()) {
                        $this->addError($name, $validator->getError());
                        break;
                    }
                    
                    // Check rules only if field is present in data array
                    if (array_key_exists($name, $data)) {
                        $rules = $validator->getRules();
                        foreach ($rules as $r) {
                            /* @var $r RuleInterface */
                            if (! $r->setObject($this->getObject())->check($data[$name])) {
                                $this->addError($name, $r->getError());
                                if ($r->getImportance() == RuleInterface::IMPORTANCE_CRITICAL) {
                                    break (2);
                                }
                            }
                        }
                    }
                } else {
                    throw new \InvalidArgumentException("Config must contain objects implementing FieldInterface or RuleInterface");
                }
            }
        }
        
        $this->valid = ! $this->hasErrors();
        return $this;
    }
}