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
use Minwork\Event\Traits\Connector;
use Minwork\Event\Traits\Events;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Event\Object\EventDispatcher;

/**
 * Basic implementation of ControllerInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Controller implements ControllerInterface
{
    use Connector, Events;

    /**
     * Framework object
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
    public function __construct(RequestInterface $request = null, ResponseInterface $response = null, EventDispatcherInterface $eventDispatcher = null): void
    {
        $this->setRequest($request ?? Request::createFromGlobals())
            ->setResponse($response ?? new Response())
            ->setEventDispatcher($eventDispatcher ?? new EventDispatcher())
            ->connect();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ControllerInterface::getFramework()
     */
    public function getFramework(): FrameworkInterface
    {
        return $this->framework;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ControllerInterface::setFramework()
     */
    public function setFramework(FrameworkInterface $framework): ControllerInterface
    {
        $this->framework = $framework;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ControllerInterface::getResponse()
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ControllerInterface::setResponse()
     */
    public function setResponse(ResponseInterface $response): ControllerInterface
    {
        $this->response = $response;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\ControllerInterface::getRequest()
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Set request object
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
