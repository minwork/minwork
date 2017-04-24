<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Object;

use Minwork\Event\Interfaces\EventInterface;

/**
 * Special event class designed for use within operations
 *
 * @author Christopher Kalkhoff
 *        
 */
class OperationEvent implements EventInterface
{

    /**
     * Operation name
     * 
     * @var string
     */
    protected $name;

    /**
     * Operation arguments
     * 
     * @var array
     */
    protected $arguments;

    /**
     * If event is active
     * 
     * @var bool
     */
    protected $active;

    /**
     * Operation result
     * 
     * @var mixed
     */
    protected $result = null;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->active = true;
    }

    /**
     * Get operation arguments
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set operation arguments
     * 
     * @param array $arguments            
     * @param string $merge            
     * @return self
     */
    public function setArguments(array $arguments, $merge = false): self
    {
        $this->arguments = $merge ? array_merge($this->arguments, $arguments) : $arguments;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Event\Interfaces\EventInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Event\Interfaces\EventInterface::setActive()
     */
    public function setActive(bool $active): EventInterface
    {
        $this->active = $active;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Event\Interfaces\EventInterface::isActive()
     */
    public function isActive(): bool
    {
        return $this->active && ! $this->hasResult();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Framework\Event\Interfaces\EventInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Set operation result
     *
     * @param mixed $result            
     * @return self
     */
    public function setResult($result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Get operation result
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * If operation finished and has result
     *
     * @return bool
     */
    public function hasResult(): bool
    {
        return ! is_null($this->result);
    }
}