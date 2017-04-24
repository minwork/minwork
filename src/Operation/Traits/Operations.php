<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Traits;

use Minwork\Operation\Object\OperationQueueObject;
use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Operation\Interfaces\QueueableObjectOperationInterface;
use Minwork\Operation\Interfaces\RevertableObjectOperationInterface;

/**
 * Trait used for object utilizing operations
 * 
 * @author Christopher Kalkhoff
 *        
 */
trait Operations
{

    /**
     * History of all executed operations
     * 
     * @var array
     */
    protected $operationHistory = [];

    /**
     * Operations queue
     * 
     * @var array
     */
    protected $operationQueue = [];

    /**
     * Operations revert queue
     * 
     * @var array
     */
    protected $revertOperationQueue = [];

    /**
     * Execute given operation
     * 
     * @param OperationInterface $operation            
     * @param array $arguments            
     * @return mixed
     */
    public function executeOperation(OperationInterface $operation, array $arguments)
    {
        $return = $operation->execute($this, $arguments);
        array_push($this->operationHistory, $operation);
        return $return;
    }

    /**
     * Append operation to queue
     *
     * @param OperationInterface $operation            
     * @param array $arguments            
     * @return QueueableObjectOperationInterface
     */
    public function addToQueue(OperationInterface $operation, array $arguments): QueueableObjectOperationInterface
    {
        array_push($this->operationQueue, new OperationQueueObject($operation, $arguments));
        return $this;
    }

    /**
     * Prepend operation to revert queue
     * 
     * @param OperationInterface $operation            
     * @param array $arguments            
     * @return RevertableObjectOperationInterface
     */
    public function addToRevertQueue(OperationInterface $operation, array $arguments): RevertableObjectOperationInterface
    {
        array_unshift($this->revertOperationQueue, new OperationQueueObject($operation, $arguments));
        return $this;
    }

    /**
     * Execute operations queue
     *
     * @param bool $clear
     *            If queue should be cleared afterwards
     *            
     * @return array
     */
    public function executeQueue(bool $clear = false)
    {
        foreach ($this->operationQueue as $operationQueueObject) {
            /* @var $operationQueueObject OperationQueueObject */
            /* @var $operation AbstractOperation */
            $operation = $operationQueueObject->getOperation();
            $arguments = $operationQueueObject->getArguments();
            $operationQueueObject->setResult($this->executeOperation($operation, $arguments));
            if ($operation->canRevert()) {
                array_unshift($this->revertOperationQueue, $operationQueueObject);
            }
        }
        $result = $this->operationQueue;
        if ($clear) {
            $this->operationQueue = [];
        }
        return $result;
    }

    /**
     * Execute revert operations queue
     *
     * @param bool $clear
     *            If queue should be cleared afterwards
     *            
     * @return array
     */
    public function revertQueue(bool $clear = false)
    {
        foreach ($this->revertOperationQueue as $operationQueueObject) {
            /* @var $operationQueueObject OperationQueueObject */
            /* @var $operation AbstractOperation */
            $operation = $operationQueueObject->getOperation();
            $arguments = $operationQueueObject->getArguments();
            if ($operation->canRevert()) {
                $operationQueueObject->setResult($operation->revert($this, $arguments));
            }
        }
        $result = $this->revertOperationQueue;
        if ($clear) {
            $this->revertOperationQueue = [];
        }
        return $result;
    }
}

