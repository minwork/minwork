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
     * Get page number
     * 
     * @return int
     */
    public function getPage(): int;

    /**
     * Get language code
     * 
     * @return string
     */
    public function getLang(): string;

    /**
     * Get controller object
     * 
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface;

    /**
     * Get controller method name
     * 
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get controller method arguments
     * 
     * @return array
     */
    public function getMethodArguments(): array;

    /**
     * Translate url to set of params accessed by getters
     * 
     * @param string $url            
     * @param bool $sanitize            
     * @return self
     */
    public function translateUrl(string $url, bool $sanitize = true): self;
}