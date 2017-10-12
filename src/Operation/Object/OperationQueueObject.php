<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Object;

use Minwork\Operation\Interfaces\OperationInterface;

/**
 * Helper for operations queue
 *
 * @author Christopher Kalkhoff
 *        
 */
class OperationQueueObject
{

    /**
     * Operation object
     *
     * @var OperationInterface
     */
    protected $operation;

    /**
     * Operation arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * If operation was executed
     *
     * @var bool
     */
    protected $executed = false;

    /**
     * Operation result
     *
     * @var mixed
     */
    protected $result = null;

    /**
     *
     * @param Operation $operation            
     * @param array $arguments            
     */
    public function __construct(Operation $operation, array $arguments)
    {
        $this->operation = $operation;
        $this->arguments = $arguments;
    }

    /**
     * Get operation object
     *
     * @return \Minwork\Operation\Interfaces\OperationInterface
     */
    public function getOperation(): OperationInterface
    {
        return $this->operation;
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
     * Set operation result
     *
     * @param mixed $result            
     * @return self
     */
    public function setResult($result): self
    {
        $this->result = $result;
        $this->executed = true;
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
     * If operation is executed
     *
     * @param bool $reset
     *            If executed property should be set to false
     * @return bool
     */
    public function isExecuted(bool $reset = true): bool
    {
        $tmp = $this->executed;
        if ($reset) {
            $this->executed = false;
        }
        return $tmp;
    }
}