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

/**
 * Abstract operation for handling CRUD
 *
 * @author Christopher Kalkhoff
 *        
 */
class Operation implements OperationInterface, EventDispatcherContainerInterface
{
    use Events;

    const EVENT_BEFORE_PREFIX = "before";

    const EVENT_AFTER_PREFIX = "after";

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

    /**
     * Operation constructor
     *
     * @param string $name
     *            Operation name
     * @param bool $canQueue
     *            Decides if operation can be added to the queue. If not, it will be executed immidiately
     * @param bool $canRevert
     *            Decides if operation can be reverted. If so, it need to implement RevertableOperationInterface
     * @param EventDispatcherInterface $eventDispatcher
     *            Event dispatcher for before and after execution events
     * @see \Minwork\Operation\Interfaces\RevertableOperationInterface
     */
    public function __construct(string $name, bool $canQueue = false, bool $canRevert = false, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->setName($name)
            ->setCanQueue($canQueue)
            ->setCanRevert($canRevert)
            ->setEventDispatcher($eventDispatcher ?? EventDispatcher::getGlobal());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::canQueue()
     */
    public function canQueue(): bool
    {
        return $this->canQueue;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::canRevert()
     */
    public function canRevert(): bool
    {
        return $this->canRevert;
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
     * Set operation name
     *
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set if operation can be reverted
     *
     * @return self
     */
    protected function setCanRevert(bool $bool): self
    {
        $this->canRevert = (bool) $bool;
        return $this;
    }

    /**
     * Set if operation can be queued
     *
     * @return self
     */
    protected function setCanQueue(bool $bool): self
    {
        $this->canQueue = boolval($bool);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::execute()
     */
    public function execute(ObjectOperationInterface $object, ...$arguments)
    {
        $methodName = $this->getName();
        $eventBefore = new OperationEvent(self::EVENT_BEFORE_PREFIX . ucfirst($methodName), $arguments);
        $this->getEventDispatcher()->dispatch($eventBefore);
        if (! $this->hasResult() && $eventBefore->hasResult()) {
            $this->result = $eventBefore->getResult();
        }
        // Use eventBefore arguments cause they may changed in before method
        $arguments = $eventBefore->getArguments();
        
        if (! $this->hasResult() && method_exists($object, $methodName)) {
            $this->result = $object->$methodName(...$arguments);
        }
        
        $eventAfter = new OperationEvent(self::EVENT_AFTER_PREFIX . ucfirst($methodName), $arguments);
        $this->getEventDispatcher()->dispatch($eventAfter);
        if (! $this->hasResult() && $eventAfter->hasResult()) {
            $this->result = $eventAfter->getResult();
        }
        
        return $this->result;
    }
}