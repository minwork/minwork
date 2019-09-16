<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Traits;

use Minwork\Basic\Exceptions\ModelException;
use Minwork\Operation\Interfaces\ObjectOperationInterface;
use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Operation\Interfaces\QueueableObjectOperationInterface;
use Minwork\Operation\Interfaces\RevertableObjectOperationInterface;
use Minwork\Operation\Interfaces\RevertableOperationInterface;
use Minwork\Operation\Object\OperationQueueObject;

/**
 * Trait used for utilizing operations inside object
 *
 * @author Christopher Kalkhoff
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
     * Execute supplied operation
     *
     * @param OperationInterface $operation
     * @param mixed ...$arguments
     * @return mixed
     * @see \Minwork\Operation\Interfaces\ObjectOperationInterface::executeOperation()
     */
    public function executeOperation(OperationInterface $operation, ...$arguments)
    {
        if ($this instanceof ObjectOperationInterface) {
            $return = $operation->execute($this, ...$arguments);
            array_push($this->operationHistory, $operation);
            return $return;
        }

        throw ModelException::missingOperationInterface(ObjectOperationInterface::class);
    }

    /**
     * Add operation to the queue
     *
     * @param OperationInterface $operation
     * @param mixed ...$arguments
     * @return QueueableObjectOperationInterface
     */
    public function addToQueue(OperationInterface $operation, ...$arguments): QueueableObjectOperationInterface
    {
        if ($this instanceof QueueableObjectOperationInterface) {
            array_push($this->operationQueue, new OperationQueueObject($operation, $arguments));
            return $this;
        }

        throw ModelException::missingOperationInterface(QueueableObjectOperationInterface::class);
    }

    /**
     * Prepend operation to the revert queue
     *
     * @param OperationInterface $operation
     * @param mixed ...$arguments
     * @return RevertableObjectOperationInterface
     */
    public function addToRevertQueue(OperationInterface $operation, ...$arguments): RevertableObjectOperationInterface
    {
        if ($this instanceof RevertableObjectOperationInterface) {
            array_unshift($this->revertOperationQueue, new OperationQueueObject($operation, $arguments));
            return $this;
        }

        throw ModelException::missingOperationInterface(RevertableObjectOperationInterface::class);
    }

    /**
     * Execute operations queue
     *
     * @param bool $clear
     *            If queue should be cleared afterwards
     * @return array
     */
    public function executeQueue(bool $clear = false)
    {
        foreach ($this->operationQueue as $operationQueueObject) {
            /* @var $operationQueueObject OperationQueueObject */
            /* @var $operation OperationInterface */
            $operation = $operationQueueObject->getOperation();
            $arguments = $operationQueueObject->getArguments();
            $operationQueueObject->setResult($this->executeOperation($operation, ...$arguments));
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
     * @return array
     */
    public function revertQueue(bool $clear = false)
    {
        foreach ($this->revertOperationQueue as $operationQueueObject) {
            /* @var $operationQueueObject OperationQueueObject */
            /* @var $operation RevertableOperationInterface */
            $operation = $operationQueueObject->getOperation();
            $arguments = $operationQueueObject->getArguments();
            if ($operation->canRevert()) {
                if ($this instanceof RevertableObjectOperationInterface) {
                    $operationQueueObject->setResult($operation->revert($this, ...$arguments));
                } else {
                    throw ModelException::missingOperationInterface(RevertableObjectOperationInterface::class);
                }
            }
        }
        $result = $this->revertOperationQueue;
        if ($clear) {
            $this->revertOperationQueue = [];
        }
        return $result;
    }
}