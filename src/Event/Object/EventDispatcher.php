<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Object;

use Minwork\Event\Interfaces\EventInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;

/**
 * Basic implementation of event dispatcher interface
 *
 * @author Christopher Kalkhoff
 *        
 */
class EventDispatcher implements EventDispatcherInterface
{

    /**
     * Global event dispatcher object
     *
     * @var EventDispatcherInterface
     */
    protected static $global;

    /**
     * List of event listeners
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Get global object or create it if necessary
     *
     * @return self
     */
    public static function getGlobal(): self
    {
        if (! isset(self::$global)) {
            self::$global = new self();
        }
        return self::$global;
    }

    /**
     * Make current object accessible statically by getGlobal function
     *
     * @return self
     */
    public function makeGlobal(): self
    {
        self::$global = $this;
        return $this;
    }

    /**
     * Trigger given event
     *
     * @param EventInterface $event            
     * @return self
     */
    public function dispatch(EventInterface $event): EventDispatcherInterface
    {
        $name = $event->setActive(true)->getName();
        if (array_key_exists($name, $this->listeners)) {
            $listenersList = $this->listeners[$name];
            // Sort by priority
            ksort($listenersList);
            foreach ($listenersList as $listeners) {
                foreach ($listeners as $listener) {
                    call_user_func($listener, $event);
                    if (! $event->isActive()) {
                        break (2);
                    }
                }
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventDispatcherInterface::addListener($event, $listener, $priority)
     */
    public function addListener(string $event, callable $listener, int $priority = 0): EventDispatcherInterface
    {
        $this->listeners[$event][$priority][] = $listener;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventDispatcherInterface::removeListener($event, $listener)
     */
    public function removeListener(string $event, callable $listener = null): EventDispatcherInterface
    {
        if (array_key_exists($event, $this->listeners)) {
            if (!is_null($listener)) {
                foreach ($this->listeners[$event] as $priority => $listeners) {
                    if (($key = array_search($listener, $listeners)) !== false) {
                        unset($this->listeners[$event][$priority][$key]);
                    }
                }    
            } else {
                unset($this->listeners[$event]);
            }
            
        }
        return $this;
    }
}