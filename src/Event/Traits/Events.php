<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Traits;

use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;

/**
 * Getter and setter for event dispatcher stored within object.
 * This trait implement all methods from EventDispatcherContainerInterface
 *
 * @see \Minwork\Event\Interfaces\EventDispatcherContainerInterface
 * @author Christopher Kalkhoff
 *        
 */
trait Events {

    /**
     * Event dispatcher object
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Set event dispatcher object
     *
     * @param EventDispatcherInterface $eventDispatcher            
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): EventDispatcherContainerInterface
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * Get event dispatcher object
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }
}