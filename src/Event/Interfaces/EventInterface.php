<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Interfaces;

/**
 * Interface for event object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface EventInterface
{

    /**
     * Get event name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set if event is active and should propagate to awaiting listeners
     *
     * @param bool $active            
     * @return self
     */
    public function setActive(bool $active): self;

    /**
     * If event is active
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * String representation of the event - usually same as name
     *
     * @return string
     */
    public function __toString(): string;
}