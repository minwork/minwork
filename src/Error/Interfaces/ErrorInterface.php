<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Interfaces;

/**
 * Error objects interfaces
 *
 * @author Krzysztof Kalkhoff
 *        
 */
interface ErrorInterface
{

    /**
     * Get string representation of error message
     */
    public function __toString(): string;

    /**
     * Get string representation of error message
     */
    public function getMessage(): string;

    /**
     * Get type of error
     */
    public function getType(): string;

    /**
     * If error has set field name
     *
     * @return bool
     */
    public function hasFieldName(): bool;

    /**
     * Set error field name
     *
     * @param string $name            
     * @return self
     */
    public function setFieldName(string $name): self;

    /**
     * Get error field name
     *
     * @return string
     */
    public function getFieldName(): string;
}
