<?php
namespace Minwork\Event\Interfaces;

interface EventDispatcherContainerInterface
{

    /**
     * Set event dispatcher object
     * 
     * @param EventDispatcherInterface $eventDispatcher            
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self;

    /**
     * Get event dispatcher object
     * 
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface;
}