<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Http\Interfaces\RequestInterface;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;

/**
 * Every controller must implement that interface
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ControllerInterface extends EventDispatcherContainerInterface
{

    /**
     * Get framework object
     *
     * @param string $refresh            
     * @return FrameworkInterface
     */
    public function getFramework(): FrameworkInterface;

    /**
     * This method sets Framework object which enable Controller to use its context
     *
     * @param FrameworkInterface $framework            
     * @return self
     */
    public function setFramework(FrameworkInterface $framework): self;

    /**
     * Get response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * Set response object
     *
     * @param ResponseInterface $response            
     * @return self
     */
    public function setResponse(ResponseInterface $response): self;

    /**
     * Get request object
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * Set response object
     * 
     * @param RequestInterface $request            
     * @return self
     */
    public function setRequest(RequestInterface $request): self;
}