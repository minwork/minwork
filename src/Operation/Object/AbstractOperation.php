<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Object;

use Minwork\Operation\Interfaces\OperationInterface;
use Minwork\Event\Traits\Events;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Operation\Interfaces\ObjectOperationInterface;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Operation\Interfaces\RevertableOperationInterface;

/**
 * Abstract operation for CRUD
 * 
 * @author Christopher Kalkhoff
 *        
 */
abstract class AbstractOperation implements OperationInterface, EventDispatcherContainerInterface
{

    const BEFORE_EVENT_PREFIX = "before";

    const AFTER_EVENT_PREFIX = "after";

    /**
     * Operation name
     * 
     * @var string
     */
    protected $name;

    /**
     * If operation can be reverted
     * 
     * @var bool
     */
    protected $canRevert;

    /**
     * If operation can be queued
     * 
     * @var bool
     */
    protected $canQueue;

    /**
     * Stores operation result
     * 
     * @var mixed $result
     */
    protected $result = null;
    
    use Events;

    /**
     * Operation constructor
     * @param string $name Operation name
     * @param bool $canQueue Decides if operation can be added to the queue. If not, it will be executed immidiately 
     * @param bool $canRevert Decides if operation can be reverted. If so, it need to implement RevertableOperationInterface
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher for before and after execution events
     * @see \Minwork\Operation\Interfaces\RevertableOperationInterface
     */
    public function __construct(string $name, bool $canQueue = true, bool $canRevert = false, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->setName($name)
            ->setCanQueue($canQueue)
            ->setCanRevert($canRevert)
            ->setEventDispatcher($eventDispatcher ?? EventDispatcher::getGlobal());
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
    protected function hasResult(): bool
    {
        return ! is_null($this->result);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::setName()
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::canRevert()
     */
    public function canRevert()
    {
        return $this->canRevert;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::setCanRevert()
     */
    public function setCanRevert($bool)
    {
        $this->canRevert = (bool) $bool;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::canQueue()
     */
    public function canQueue()
    {
        return $this->canQueue;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::setCanQueue()
     */
    public function setCanQueue($bool)
    {
        $this->canQueue = (bool) $bool;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::execute()
     */
    public function execute(ObjectOperationInterface $object, array $arguments)
    {
        $methodName = mb_strtolower($this->getName());
        $eventBefore = new OperationEvent(self::BEFORE_EVENT_PREFIX . ucfirst($methodName), $arguments);
        
        
        $this->getEventDispatcher()->dispatch($eventBefore);
        if (! $this->hasResult() && $eventBefore->hasResult()) {
            $this->result = $eventBefore->getResult();
        }
        
        // Use eventBefore arguments cause they may changed in before method
        $arguments = $eventBefore->getArguments();
        if (! $this->hasResult() && method_exists($object, $methodName)) {
            
            $this->result = call_user_func_array([
                $object,
                $methodName
            ], $arguments);
        }
        
        $eventAfter = new OperationEvent(self::AFTER_EVENT_PREFIX . ucfirst($methodName), $arguments);
        $this->getEventDispatcher()->dispatch($eventAfter);
        if (! $this->hasResult() && $eventAfter->hasResult()) {
            $this->result = $eventAfter->getResult();
        }
        
        return $this->result;
    }
}