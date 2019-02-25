<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Core;

use Minwork\Basic\Interfaces\FrameworkInterface;
use Minwork\Basic\Utility\FlowEvent;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Event\Object\Event;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Event\Traits\Events;
use Minwork\Helper\Formatter;
use Minwork\Http\Interfaces\EnvironmentInterface;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Http\Interfaces\RouterInterface;
use Minwork\Http\Object\Response;
use Minwork\Http\Utility\HttpCode;

/**
 * Basic implementation of FrameworkInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Framework implements FrameworkInterface, EventDispatcherContainerInterface
{
    use Events;

    const EVENT_AFTER_URL_TRANSLATION = 'afterUrlTranslation';

    const EVENT_BEFORE_METHOD_RUN = 'beforeMethodRun';

    const EVENT_AFTER_METHOD_RUN = 'afterMethodRun';

    const EVENT_BEFORE_CONTROLLER_RUN = 'beforeRun';

    const EVENT_AFTER_CONTROLLER_RUN = 'afterRun';

    const EVENT_BEFORE_CONTENT_OUTPUT = 'beforeOutput';

    const EVENT_BEFORE_REDIRECT = 'beforeRedirect';

    const EVENTS = [
        self::EVENT_AFTER_URL_TRANSLATION,
        self::EVENT_BEFORE_METHOD_RUN,
        self::EVENT_AFTER_METHOD_RUN,
        self::EVENT_BEFORE_CONTROLLER_RUN,
        self::EVENT_AFTER_CONTROLLER_RUN,
        self::EVENT_BEFORE_CONTENT_OUTPUT,
        self::EVENT_BEFORE_REDIRECT
    ];

    /**
     * Router object
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * Environment object
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     *
     * @param RouterInterface $router            
     * @param EnvironmentInterface $environment            
     * @param EventDispatcherInterface $eventDisptacher            
     */
    public function __construct(RouterInterface $router, EnvironmentInterface $environment, EventDispatcherInterface $eventDisptacher = null)
    {
        $this->setEventDispatcher($eventDisptacher ?? EventDispatcher::getGlobal())
            ->setRouter($router)
            ->setEnvironment($environment);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\FrameworkInterface::getRouter()
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\FrameworkInterface::setRouter()
     */
    public function setRouter(RouterInterface $router): FrameworkInterface
    {
        $this->router = $router;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\FrameworkInterface::getEnvironment()
     */
    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\FrameworkInterface::setEnvironment()
     */
    public function setEnvironment(EnvironmentInterface $environment): FrameworkInterface
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Output response received from controller method
     *
     * @param ResponseInterface $response
     * @param bool $return
     * @return mixed
     */
    protected function output(ResponseInterface $response, bool $return)
    {
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_CONTENT_OUTPUT));

        if ($return) {
            return $response->getContent();
        }

        foreach ($response->getHeaders() as $header) {
            header($header);
        }
        
        http_response_code($response->getHttpCode());
        echo strval($response->getContent());

        return $response;
    }

    /**
     * Run application based on supplied url
     *
     * Browser output or this method return value (@see $return) depends on Controller response object.
     * Response can be set directly or its content can be set by returning it from controller method
     *
     * @param string $url
     * @param bool $return
     *            If response should be returned from this method instead of outputting it to the browser
     * @param bool $sanitize
     *            If url should be sanitized
     * @return mixed
     */
    public function run(string $url, bool $return = false, bool $sanitize = true)
    {
        $controllerName = $this->getRouter()->getControllerName();
        $controller = $this->getRouter()
            ->translateUrl($url, $sanitize)
            ->getController()
            ->setFramework($this);
        
        // All events should start here so controller can intercept them
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_AFTER_URL_TRANSLATION));

        $event = new FlowEvent(self::EVENT_BEFORE_CONTROLLER_RUN, $controllerName);
        $controller->getEventDispatcher()->dispatch($event);
        
        // If response was set by any event listener then directly output content of the response
        if ($event->shouldBreakFlow()) {
            return $this->output($controller->getResponse(), $return);
        }
        
        $method = $this->getRouter()->getMethod();

        $arguments = $this->getRouter()->getMethodArguments();

        $event = new FlowEvent(self::EVENT_BEFORE_METHOD_RUN, $method, $arguments);
        $controller->getEventDispatcher()->dispatch($event);

        // If response was set by any event listener then directly output content of the response
        if ($event->shouldBreakFlow()) {
            return $this->output($controller->getResponse(), $return);
        }
        
        // Get content
        $args = array_values($arguments);
        $content = $controller->$method(...$args);

        // If controller returned some value then convert it to response
        if ($controller->getResponse()->isEmpty()) {
            // Cast whole content to response if it was empty
            $controller->setResponse(Response::createFrom($content));
        } else {
            // Or only set it as content if it wasn't
            $controller->getResponse()->setContent($content);
        }
        
        $controller->getEventDispatcher()->dispatch(new FlowEvent(self::EVENT_AFTER_METHOD_RUN, $method, $arguments));
        
        $controller->getEventDispatcher()->dispatch(new FlowEvent(self::EVENT_AFTER_CONTROLLER_RUN, $controllerName));
        
        return $this->output($controller->getResponse(), $return);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\FrameworkInterface::redirect()
     */
    public function redirect(string $address, bool $external = false): ResponseInterface
    {
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_REDIRECT));
        $address = $external ? Formatter::makeUrl($address) : $this->getEnvironment()->getDomain() . $address;

        return (new Response())->setHttpCode(HttpCode::FOUND)->setHeader("Location: {$address}", false);
    }
}
