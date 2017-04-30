<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Object;

use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator as ValidatorTrait;

/**
 * Basic implementation of validator interface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Validator implements ValidatorInterface
{
    use ValidatorTrait;

    /**
     * Validation config which is a list of objects implementing ValidatorInterface
     *
     * @var ValidatorInterface[]
     */
    protected $config;

    /**
     * Initialize validator with array of objects implementing ValidatorInterface
     *
     * @param ValidatorInterface[] $config            
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Set validator config which is list of ValidatorInterface objects
     *
     * @param ValidatorInterface[] $config            
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setConfig(array $config): self
    {
        foreach ($config as $validator) {
            if (! is_object($validator) || ! $validator instanceof ValidatorInterface) {
                throw new \InvalidArgumentException('Config elements must be objects implementing ValidatorInterface');
            }
        }
        $this->config = $config;
        return $this;
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
        } else {
            foreach ($this->config as $validator) {
                if ($this->hasContext()) {
                    $validator->setContext($this->getContext());
                }
                
                if (! $validator->validate($data)->isValid()) {
                    $this->getErrors()->merge($validator->getErrors());
                    if (method_exists($validator, 'hasCriticalError') && $validator->hasCriticalError()) {
                        break;
                    }
                }
            }
        }
        
        $this->valid = ! $this->hasErrors();
        return $this;
    }
}