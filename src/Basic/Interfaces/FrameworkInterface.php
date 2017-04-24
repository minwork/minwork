<?php
namespace Minwork\Basic\Interfaces;

use Minwork\Http\Interfaces\RouterInterface;
use Minwork\Http\Interfaces\EnvironmentInterface;

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
     * Redirects to specified url address<br>
     * Local address should start with '/' and has Environment domain prepended<br>
     * External address can omit protocol ('http(s)://') address part cause it should be automatically prepended if not specified 
     *
     * @param string $address
     */
    public function redirect(string $address, bool $external = false);
}