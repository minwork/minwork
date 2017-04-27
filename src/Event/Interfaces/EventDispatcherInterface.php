<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Interfaces;

/**
 * Interface for event disptacher object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface EventDispatcherInterface
{

    const DEFAULT_PRIORITY = 100;

    /**
     * Trigger given event by notifying every listener bond to its name
     *
     * @param EventInterface $event            
     * @return self
     */
    public function dispatch(EventInterface $event): self;

    /**
     * Register new event listener
     *
     * @param string $event
     *            Event name
     * @param callable $listener
     *            Function that can handle dispatched event object
     * @param int $priority
     *            Priority on handling dispatched event within listeners bond to it
     * @return self
     */
    public function addListener(string $event, callable $listener, int $priority = self::DEFAULT_PRIORITY): self;

    /**
     * Remove event listener
     *
     * @param string $event
     *            Event name
     * @param callable $listener
     *            Function that can handle dispatched event object
     * @return self
     */
    public function removeListener(string $event, callable $listener): self;
}