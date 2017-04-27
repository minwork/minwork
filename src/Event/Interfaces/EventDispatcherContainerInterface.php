<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Interfaces;

/**
 * Interface for a class that contain event dispatcher object
 * 
 * @author Christopher Kalkhoff
 *        
 */
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