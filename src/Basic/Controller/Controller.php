<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Controller;

use Minwork\Http\Object\Response;
use Minwork\Http\Object\Request;
use Minwork\Basic\Interfaces\ControllerInterface;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Http\Interfaces\RequestInterface;
use Minwork\Basic\Interfaces\FrameworkInterface;

/**
 * Basic controller used by framework
 *
 * @author Christopher Kalkhoff
 *        
 */
class Controller implements ControllerInterface
{

    /**
     * Framework reference
     *
     * @var FrameworkInterface
     */
    protected $framework;

    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Response object
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Optionally takes request and response objects as arguments
     *
     * @param RequestInterface $request            
     * @param ResponseInterface $response            
     */
    public function __construct(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->setRequest($request ?? Request::createFromGlobals())->setResponse($response ?? new Response());
    }

    /**
     * Get framework
     *
     * @param string $refresh            
     * @return Framework
     */
    public function getFramework(): FrameworkInterface
    {
        return $this->framework;
    }

    /**
     * Set framework object
     *
     * @param Framework $framework            
     * @return self
     */
    public function setFramework(FrameworkInterface $framework): ControllerInterface
    {
        $this->framework = $framework;
        return $this;
    }

    /**
     * Get response
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set response object
     *
     * @param ResponseInterface $response            
     * @return self
     */
    public function setResponse(ResponseInterface $response): ControllerInterface
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     *
     * @param Request $request            
     * @return self
     */
    public function setRequest(RequestInterface $request): ControllerInterface
    {
        $this->request = $request;
        return $this;
    }
}
