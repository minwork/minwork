<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

use Minwork\Basic\Interfaces\ControllerInterface;

/**
 * Interface for router object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface RouterInterface
{

    /**
     * Get current url
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * If url has page param in it
     * 
     * @return bool
     */
    public function hasPage(): bool;
    
    /**
     * Get page number from url params
     *
     * @return int
     */
    public function getPage(): int;

    /**
     * Get language code from url params
     *
     * @return string
     */
    public function getLang(): string;

    /**
     * Get controller object extracted from url
     *
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface;

    /**
     * Get controller name extracted from url
     * 
     * @return string
     */
    public function getControllerName(): string;

    /**
     * Get controller method name extracted from url
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get controller method arguments extracted from url
     *
     * @return array
     */
    public function getMethodArguments(): array;

    /**
     * Translate url to set of params accessed by getters
     *
     * @param string $url            
     * @param bool $sanitize
     *            If url should be cleaned from any special chars
     * @return self
     */
    public function translateUrl(string $url, bool $sanitize = true): self;
}