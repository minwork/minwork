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
use Minwork\Operation\Interfaces\ObjectOperationInterface;
use Minwork\Operation\Interfaces\RevertableObjectOperationInterface;
use Minwork\Basic\Interfaces\ModelInterface;

/**
 * Update operation
 *
 * @author Christopher Kalkhoff
 *        
 */
class Update extends AbstractOperation implements RevertableOperationInterface
{

    const OPERATION_NAME = "update";

    const EVENT_BEFORE = "beforeUpdate";

    const EVENT_AFTER = "afterUpdate";

    /**
     * Stores data for recovery if reverted
     *
     * @var mixed
     */
    protected $previousData = null;

    /**
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
    public function __construct(string $name = self::OPERATION_NAME, bool $canQueue = true, bool $canRevert = false, EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($name, $canQueue, $canRevert, $eventDispatcher);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\OperationInterface::execute()
     */
    public function execute(ObjectOperationInterface $object, array $arguments)
    {
        // If object implements ModelInterface it is possible to be reverted otherwise disable it
        if ($object instanceof ModelInterface && $this->canRevert()) {
            $data = reset($arguments);
            if (is_array($data)) {
                // Save current model data before executing update
                $this->previousData = $object->getData(array_keys($data));
            } else {
                $this->setCanRevert(false);
            }
        }
        return parent::execute($object, $arguments);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Operation\Interfaces\RevertableOperationInterface::revert()
     */
    public function revert(RevertableObjectOperationInterface $object, array $arguments)
    {
        if ($this->canRevert() && ! is_null($this->previousData)) {
            $arguments[0] = $this->previousData;
            return $object->executeOperation(new Update($this->getEventDispatcher()), $arguments);
        }
        return false;
    }
}