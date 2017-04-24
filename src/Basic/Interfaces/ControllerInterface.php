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

/**
 * Every controller must implement that interface
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface ControllerInterface
{

    /**
     * Get framework
     *
     * @param string $refresh            
     * @return Framework
     */
    public function getFramework(): FrameworkInterface;

    /**
     * This method sets Framework object which enable Controller to use its context
     *
     * @param Framework $framework            
     * @return self
     */
    public function setFramework(FrameworkInterface $framework): self;

    /**
     * Get response
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
     * Get request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     *
     * @param RequestInterface $request            
     * @return Controller
     */
    public function setRequest(RequestInterface $request): self;
}