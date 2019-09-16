<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Basic;

use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Operation\Object\Operation;

/**
 * Read operation
 *
 * @author Christopher Kalkhoff
 *        
 */
class Read extends Operation
{

    const OPERATION_NAME = "read";

    const EVENT_BEFORE = "beforeRead";

    const EVENT_AFTER = "afterRead";

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
    public function __construct(string $name = self::OPERATION_NAME, bool $canQueue = true, bool $canRevert = false, EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($name, $canQueue, $canRevert, $eventDispatcher);
    }
}