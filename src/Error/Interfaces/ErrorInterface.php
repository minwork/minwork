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
 */
interface ErrorInterface
{

    /**
     * Get string representation of error message
     * 
     * @return string
     */
    public function __toString(): string;

    /**
     * Get error message
     * 
     * @return string
     */
    public function getMessage(): string;

    /**
     * Get error type
     * 
     * @return string
     */
    public function getType(): string;

    /**
     * Get list of additional error data
     *
     * @return array
     */
    public function getData(): array;

    /**
     * If error is referring to something
     *
     * @return bool
     */
    public function hasRef(): bool;

    /**
     * Set reference to something
     *
     * @param mixed $ref
     * @return self
     */
    public function setRef($ref): self;

    /**
     * Get error reference
     *
     * @return mixed
     */
    public function getRef();
}
