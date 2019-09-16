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
use Minwork\Event\Object\EventDispatcher;
use Minwork\Helper\Arr;
use Minwork\Helper\Formatter;
use Minwork\Operation\Object\Operation;
use ReflectionClass;

/**
 * Trait used for automatic event listeners binding
 *
 * @author Christopher Kalkhoff
 *        
 */
trait Connector {

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Connect events to dispatcher
     *
     * @param null|string|object|array $connector
     *            <br><br><u>Available formats</u>:<br><br>
     *            <i>null</i>
     *            <p>Automatically connect operation events with class methods which name starts with 'before' or 'after' phrase</p>
     *            <i>array</i>
     *            <ul>
     *              <li>
     *                  Associative array: event to methods
     *                  <p><pre>[{event_name} => {method_name}, ...]</pre></p>
     *              </li>
     *              <li>
     *                  List of events
     *                  <p><pre>[{event_name1}, {event_name2}, ...]</pre></p>
     *              </li>
     *            </ul>
     *            <i>string|object</i>
     *            Object or string representing class name
     *            <p>Search list of class constants and match any constant name that starts with 'event' keyword<br>
     *            If constant is matched its value will be trimmed from 'event' keyword otherwise raw value is used</p>
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     * @return self
     */
    protected function connect($connector = null, EventDispatcherInterface $eventDispatcher = null)
    {
        $eventDispatcher = $eventDispatcher ?? ($this instanceof EventDispatcherContainerInterface ? $this->getEventDispatcher() : EventDispatcher::getGlobal());
        $methods = get_class_methods($this);
        $methodsMap = [];
        $mapper = [];
        
        // Function to normalize event name by removing all underscores and 'event' string then converting to lowercase
        $normalizedEventName = function ($event) {
            $eventName = strtr($event, '_', '');
            if (($pos = mb_stripos($eventName, 'event')) !== false) {
                $event = mb_substr($eventName, $pos + 5);
            }
            return mb_strtolower($event);
        };
        
        // Normalize list of current object methods to lowercase
        foreach ($methods as $method) {
            $methodsMap[$method] = mb_strtolower($method);
        }
        
        // If connector is object or class name string search its constants to match with current object methods
        if (is_null($connector)) {
            // Automatically map object operations
            foreach ($methods as $method) {
                if (Formatter::startsWith($method, Operation::EVENT_BEFORE_PREFIX) || Formatter::startsWith($method, Operation::EVENT_AFTER_PREFIX)) {
                    $mapper[$method][] = $method;
                }
            }
        } elseif (is_object($connector) || is_string($connector)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflection = new ReflectionClass($connector);
            $constants = $reflection->getConstants();

            foreach ($constants as $const => $event) {
                if (is_string($event) && Formatter::startsWith($const, 'event') && (($method = array_search($normalizedEventName($event), $methodsMap)) !== false || ($method = array_search($normalizedEventName($const), $methodsMap)) !== false)) {
                    $mapper[$event][] = $method;
                }
            }
            // If connector is array then map it to [event_name => current_object_method, ...]
        } elseif (is_array($connector)) {
            if (Arr::isAssoc($connector)) {
                foreach ($connector as $event => $method) {
                    $mapper[$event] = Arr::forceArray($method);
                }
            } else {
                foreach ($connector as $event) {
                    if (($method = array_search($normalizedEventName($event), $methodsMap)) !== false) {
                        $mapper[$event][] = $method;
                    }
                }
            }
        }
        
        // Add listeners according to events mapped to method names
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