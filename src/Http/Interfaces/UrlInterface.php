<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

/**
 * Interface for url object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface UrlInterface
{

    /**
     * Get url string representation (usually same as getUrl)
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get url string representation
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get url param
     *
     * @param string $name            
     * @return string
     */
    public function getParam(string $name): string;

    /**
     * Append query param(s)
     *
     * @param string|array $query            
     * @return self
     */
    public function appendQuery($query): self;
}