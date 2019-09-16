<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

use Minwork\Http\Interfaces\EnvironmentInterface;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Http\Interfaces\RouterInterface;

/**
 * Every framework must implement that interface
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface FrameworkInterface
{

    /**
     * Get router object
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface;

    /**
     * Set router object
     *
     * @param RouterInterface $router            
     * @return self
     */
    public function setRouter(RouterInterface $router): self;

    /**
     * Get application environment object
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment(): EnvironmentInterface;

    /**
     * Set environment object
     *
     * @param EnvironmentInterface $environment            
     * @return self
     */
    public function setEnvironment(EnvironmentInterface $environment): self;

    /**
     * Return response which will redirect to specified url address<br>
     * Local address should start with '/' and has Environment domain prepended<br>
     * External address can omit protocol ('http(s)://') address part cause it should be automatically prepended if not specified
     *
     * @param string $address
     * @return ResponseInterface           
     */
    public function redirect(string $address, bool $external = false): ResponseInterface;
}