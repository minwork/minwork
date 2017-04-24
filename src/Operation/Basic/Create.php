<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Basic;

use Minwork\Operation\Object\AbstractOperation;
use Minwork\Operation\Interfaces\RevertableOperationInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Operation\Interfaces\RevertableObjectOperationInterface;

/**
 * Create operation
 * 
 * @author Christopher Kalkhoff
 *        
 */
class Create extends AbstractOperation implements RevertableOperationInterface
{

    const OPERATION_NAME = "create";

    const EVENT_BEFORE = "beforeCreate";

    const EVENT_AFTER = "afterCreate";

    /**
     * Operation constructor
     * @param string $name Operation name
     * @param bool $canQueue Decides if operation can be added to the queue. If not, it will be executed immidiately 
     * @param bool $canRevert Decides if operation can be reverted. If so, it need to implement RevertableOperationInterface
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher for before and after execution events
     * @see \Minwork\Operation\Interfaces\RevertableOperationInterface
     */
    public function __construct(string $name = self::OPERATION_NAME, bool $canQueue = true, bool $canRevert = true, EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($name, $canQueue, $canRevert, $eventDispatcher);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Operation\Interfaces\RevertableOperationInterface::revert()
     */
    public function revert(RevertableObjectOperationInterface $object, array $arguments)
    {
        if (method_exists($object, Delete::OPERATION_NAME)) {
            return $object->executeOperation(new Delete($this->getEventDispatcher()), $arguments);
        }
        return false;
    }
}