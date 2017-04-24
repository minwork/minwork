<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Traits;

use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Helper\ArrayHelper;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Event\Object\EventDispatcher;

/**
 * Trait used for automatic event listeners binding
 * @author Christopher Kalkhoff
 *
 */
trait Connector {

    /**
     * Connect events to dispatcher
     * @param EventDispatcherInterface $dispatcher            
     * @param object|array $connector
     *            <br><u>Available formats</u>:<br>
     *            Associative array: event to methods
     *            <p><pre>[{event_name} => {method_name}, ...]</pre></p>
     *            List of events
     *            <p><pre>[{event_name1}, {event_name2}, ...]</pre></p>
     *            Object with constants containing 'event' keyword
     *            <p>Search list of class constants and matches any name or value that contains 'event' keyword<br>
     *            If value is matched it will be trimmed from 'event' keyword otherwise raw value is used</p>
     *            
     * @return self
     */
    protected function connect($connector = null, EventDispatcherInterface $eventDispatcher = null): self
    {
        $connector = $connector ?? $this;
        $eventDispatcher = $eventDispatcher ?? ($this instanceof EventDispatcherContainerInterface ? $this->getEventDispatcher() : new EventDispatcher());
        $methods = get_class_methods($this);
        $methodsMap = [];
        $mapper = [];
        
        $normalizedEventName = function ($event) {
            $eventName = strtr($event, '_', '');
            if (($pos = mb_stripos($eventName, 'event')) !== false) {
                $event = mb_substr($eventName, $pos + 5);
            }
            return mb_strtolower($event);
        };
        
        foreach ($methods as $method) {
            $methodsMap[$method] = mb_strtolower($method);
        }
        
        if (is_object($connector) || is_string($connector)) {
            $reflection = new \ReflectionClass($connector);
            $constants = $reflection->getConstants();
            
            foreach ($constants as $const => $event) {
                if (($method = array_search($normalizedEventName($event), $methodsMap)) !== false || ($method = array_search($normalizedEventName($const), $methodsMap)) !== false) {
                    $mapper[$event][] = $method;
                }
            }
        } elseif (is_array($connector)) {
            if (ArrayHelper::isAssoc($connector)) {
                foreach ($connector as $event => $method) {
                    $mapper[$event] = ArrayHelper::forceArray($method);
                }
            } else {
                foreach ($connector as $event) {
                    if (($method = array_search($normalizedEventName($event), $methodsMap)) !== false) {
                        $mapper[$event][] = $method;
                    }
                }
            }
        }
        
        foreach ($mapper as $eventName => $methods) {
            foreach ($methods as $method) {
                $eventDispatcher->addListener($eventName, [
                    $this,
                    $method
                ]);
            }
        }
        
        return $this;
    }
}