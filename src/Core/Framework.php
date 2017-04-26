<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Core;

use Minwork\Basic\Controller\Controller;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Event\Object\Event;
use Minwork\Event\Traits\Events;
use Minwork\Http\Interfaces\RouterInterface;
use Minwork\Event\Interfaces\EventDispatcherInterface;
use Minwork\Http\Object\Response;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Http\Object\Router;
use Minwork\Basic\Interfaces\ViewInterface;
use Minwork\Event\Interfaces\EventDispatcherContainerInterface;
use Minwork\Basic\Interfaces\FrameworkInterface;
use Minwork\Http\Interfaces\EnvironmentInterface;
use Minwork\Helper\Formatter;
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

    const EVENT_BEFORE_REDIRECT = 'EventBeforeRedirect';

    const EVENT_AFTER_URL_TRANSLATION = 'EventAfterUrlTranslation';

    const EVENT_BEFORE_METHOD_RUN = 'EventBeforeMethodRun';

    const EVENT_AFTER_METHOD_RUN = 'EventAfterMethodRun';

    const EVENT_BEFORE_CONTROLLER_RUN = 'EventBeforeRun';

    const EVENT_AFTER_CONTROLLER_RUN = 'EventAfterRun';

    const EVENT_BEFORE_OUTPUT_CONTENT = 'EventBeforeOutputContent';

    /**
     * Router object
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     *
     * @param RouterInterface $router            
     * @param EnvironmentInterface $environment            
     * @param EventDispatcherInterface $eventDisptacher            
     */
    public function __construct(RouterInterface $router, EnvironmentInterface $environment, EventDispatcherInterface $eventDisptacher = null)
    {
        $this->setRouter($router)
            ->setEnvironment($environment)
            ->setEventDispatcher($eventDisptacher ?? EventDispatcher::getGlobal());
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
     * Output response recieved from controller method
     *
     * @param mixed $response            
     * @throws \Exception
     * @return bool
     */
    protected function outputContent($response): bool
    {
        if (is_null($response)) {
            return false;
        } elseif (is_string($response) || is_numeric($response)) {
            $response = new Response($response);
        } elseif (is_object($response)) {
            if ($response instanceof ViewInterface) {
                $response = (new Response())->setObject($response);
            } elseif (! ($response instanceof ResponseInterface)) {
                throw new \Exception("Object returned in controller must implement ViewInterface or ResponseInterface");
            }
        } elseif (is_array($response)) {
            $response = new Response(print_r($response, true));
        } else {
            $response = new Response();
        }
        
        foreach ($response->getHeaders() as $header) {
            header($header);
        }
        
        http_response_code($response->getHttpCode());
        
        if (! empty($response->getContent())) {
            echo $response->getContent();
        }
        return true;
    }

    /**
     * Run application based on supplied url
     *
     * @param string $url            
     * @param bool $returnContent
     *            If content should be outputed or returned
     * @param bool $sanitize
     *            If url should be sanitized
     * @return mixed
     */
    public function run(string $url, bool $returnContent = false, bool $sanitize = true)
    {
        $controller = $this->getRouter()
            ->translateUrl($url, $sanitize)
            ->getController()
            ->setFramework($this);
        
        // All events should start here so controller can intercept them
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_AFTER_URL_TRANSLATION));
        
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_CONTROLLER_RUN));
        
        $method = $this->getRouter()->getMethod();
        
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_METHOD_RUN));
        
        $arguments = $this->getRouter()->getMethodArguments();
        
        // If response was set by any event listener then directly output content of the response
        if (! $controller->getResponse()->isEmpty()) {
            $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_OUTPUT_CONTENT));
            
            return ! $returnContent ? $this->outputContent($controller->getResponse()) : $controller->getResponse();
        }
        
        // Get content
        $content = call_user_func_array([
            $controller,
            $method
        ], array_values($arguments));
        
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_AFTER_METHOD_RUN));
        
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_AFTER_CONTROLLER_RUN));
        
        $this->getEventDispatcher()->dispatch(new Event(self::EVENT_BEFORE_OUTPUT_CONTENT));
        
        return ! $returnContent ? $this->outputContent($content) : $content;
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
        $response = new Response();
        return $response->setHttpCode(HttpCode::FOUND)->setHeader("Location: {$address}", false);
    }
}
