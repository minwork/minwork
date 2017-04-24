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
 * @author Christopher Kalkhoff
 *
 */
interface EventDispatcherInterface {
    const DEFAULT_PRIORITY = 100; 
    /**
     * Trigger given event
     * @param EventInterface $event
     * @return self
     */
    public function dispatch(EventInterface $event): self;
    /**
     * Register new event listener
     * @param string $event
     * @param callable $listener
     * @param int $priority
     * @return self
     */
    public function addListener(string $event, callable $listener, int $priority = self::DEFAULT_PRIORITY): self;
    /**
     * Remove event listener
     * @param string $event
     * @param callable $listener
     * @return self
     */
    public function removeListener(string $event, callable $listener): self;
}