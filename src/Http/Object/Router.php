<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Minwork\Http\Object;

use Minwork\Helper\Formatter;
use Minwork\Http\Exceptions\HttpException;
use Minwork\Http\Interfaces\RouterInterface;
use Minwork\Basic\Interfaces\ControllerInterface;
use Minwork\Http\Utility\LangCode;
use Minwork\Basic\Traits\Debugger;
use Minwork\Helper\Arr;
use Minwork\Core\Framework;
use Minwork\Basic\Controller\Controller;

/**
 * Basic implementation of router interface
 *
 * @author Christopher Kalkhoff
 *
 */
class Router implements RouterInterface
{
    use Debugger;

    const PARAMS_REGEX_LANG = '/^lang-(\w{2,3})$/';

    const PARAMS_REGEX_PAGE = '/^page-(?<number>\d+)$/';

    const PARAMS_REGEX_ASSOC_PARAM = '/^(\w+):(\w+)$/';

    const PARAMS_ARRAY_SEPARATOR = ',';

    const PARAMS_SEPARATOR = '/';

    const DEFAULT_CONTROLLER_ROUTE_NAME = 'default_controller';

    const DEFAULT_CONTROLLER_METHOD = 'show';

    /**
     * Currently parsed url
     *
     * @var string
     */
    protected $url;

    /**
     * Language code
     *
     * @var string|null
     */
    protected $lang;

    /**
     * Controller name
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Controller object
     *
     * @var ControllerInterface
     */
    protected $controller;

    /**
     * Controller method name
     *
     * @var string
     */
    protected $method;

    /**
     * Controller method arguments
     *
     * @var array
     */
    protected $methodArguments = [];

    /**
     * Page number (by default 1)
     *
     * @var int|null
     */
    protected $page;

    /**
     * Array containing map of controller name to controller class or object
     *
     * @var array
     */
    protected $routing = [];

    /**
     *
     * @see \Minwork\Http\Object\Router::setRouting()
     * @param array|string $routing
     */
    public function __construct($routing)
    {
        $this->reset()->setRouting(Arr::forceArray($routing));
    }

    /**
     * Reset to initial properties values
     *
     * @return self
     */
    public function reset(): self
    {
        $this->url = '';
        $this->lang = '';
        $this->controllerName = null;
        $this->controller = null;
        $this->method = null;
        $this->methodArguments = [];
        $this->page = null;
        $this->routing = [];
        return $this;
    }

    /**
     * Set routing array
     *
     * @param array $routing
     * @return self
     */
    public function setRouting(array $routing): self
    {
        foreach ($routing as $key => $route) {
            $curRouting = is_string($route) && is_file($route) ? require_once $route : [
                $key => $route
            ];

            if (is_array($curRouting)) {
                if (!array_key_exists(self::DEFAULT_CONTROLLER_ROUTE_NAME, $curRouting)) {
                    $this->debug("Routing array should contain default controller route at '" . self::DEFAULT_CONTROLLER_ROUTE_NAME . "' key");
                }
                $this->routing = array_merge($this->routing, $curRouting);
            } else {
                $this->debug("Routing entry at {$key} in file {$route} is not array - skipping this file");
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RouterInterface::getUrl()
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Minwork\Http\Interfaces\RouterInterface::hasPage()
     */
    public function hasPage(): bool
    {
        return isset($this->page);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getPage()
     */
    public function getPage(): int
    {
        return $this->page ?? 1;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getLang()
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getController()
     * @throws HttpException
     */
    public function getController(): ControllerInterface
    {
        if (!isset($this->controller)) {
            // Try fallback to default controller
            $routing = $this->routing;
            $this->parseController($this->getControllerName(), $routing);

            // If controller still doesnt exists then throw error
            if (!isset($this->controller)) {
                throw new HttpException("Cannot map url params to controller object");
            }
        }
        return $this->controller;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RouterInterface::getControllerName()
     */
    public function getControllerName(): string
    {
        return $this->controllerName ?? self::DEFAULT_CONTROLLER_ROUTE_NAME;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RouterInterface::getMethod()
     */
    public function getMethod(): string
    {
        return $this->method ?? self::DEFAULT_CONTROLLER_METHOD;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getMethodArguments()
     */
    public function getMethodArguments(): array
    {
        return $this->methodArguments;
    }

    /**
     * Translates url into set of sanitazed params (separated by `/`) as specified below:<br>
     * <ul>
     * <li>Controller (<strong>1st argument</strong>) - name of key in routing array refering to appropiate routing path</li>
     * <li>Method (<strong>2nd argument</strong>) - name of method in controller to run</li>
     * <li><strong>lang-{code}</strong> - Language, code must be specifed accoridng to Lang class codes list.<br>
     * Default set to English
     * </li>
     * <li><strong>page-{number}</strong> - Page number starting from 1.<br>
     * Defult set to 1
     * </li>
     * <li><strong>Method arguments</strong> - any other values unfitting translation rules will be trated as following arguments of method</li>
     * </ul>
     *
     * @param string $url
     *            Url string
     * @param bool $clean If input url should be cleared from potentially dangerous characters and unnecessary url parts (preserving only path)
     * @return self
     * @throws \ReflectionException
     * @throws HttpException
     */
    public function translateUrl(string $url, bool $clean = true): RouterInterface
    {
        $this->url = $url;

        // Temporary routing copy for internal iteration of prefixes
        $routing = $this->routing;
        $params = $this->parseUrl($url, $clean);

        foreach ($params as $param) {
            $this->parseParam($param, $routing);
        }

        $this->normalizeMethodArguments();

        return $this;
    }

    protected function parseUrl(string $url, bool $clean): array
    {
        if ($clean) {
            if (!$parsed = parse_url($url, PHP_URL_PATH)) {
                return [];
            }
            $url = Formatter::cleanString(Formatter::removeTrailingSlash(Formatter::removeLeadingSlash($parsed)));
        }

        if (empty($url)) {
            return [];
        }

        return explode(self::PARAMS_SEPARATOR, $url);
    }

    /**
     * @param string $param
     * @param array $routing
     * @return bool
     * @throws HttpException
     */
    protected function parseParam(string $param, array &$routing): bool
    {
        if ((preg_match(self::PARAMS_REGEX_PAGE, $param, $matches))) { // Page
            $page = intval($matches[1]);
            $this->page = $page > 0 ? $page : 1;
            return true;
        }

        if ((preg_match(self::PARAMS_REGEX_LANG, $param, $matches)) && in_array($matches[1], LangCode::CODES_LIST)) { // Language
            $this->lang = $matches[1];
            return true;
        }

        // If param doesnt match any special param type then treat it as controller name
        if (!isset($this->controller) && $this->parseController($param, $routing)) {
            return true;
        }

        // Set it as method name (if not already set)
        if (!isset($this->method) && $this->parseMethod($param)) {
            return true;
        }

        // Otherwise set it as method argument
        if ($param !== '') {
            $this->addMethodArgument($param);
            return true;
        }

        return false;
    }

    /**
     * @param string $param
     * @param array $routing
     * @return bool If param is consumed by this function
     * @throws HttpException
     */
    protected function parseController(string $param, array &$routing): bool
    {
        $controller = $param;
        // If key doesn't exists first try normalized one
        if (!array_key_exists($controller, $routing)) {
            $controller = Formatter::textId($controller);
        }
        // If it still doesn't exists then fallback to default controller name
        if (!array_key_exists($controller, $routing)) {
            $controller = self::DEFAULT_CONTROLLER_ROUTE_NAME;
            // If cannot find default controller entry in routing
            if (!array_key_exists($controller, $routing)) {
                throw new HttpException("Cannot load controller for url param: {$param}");
            }
        }

        // At this point controller must be valid entry in routing
        $entry = $routing[$controller];

        // If routing entry is array of nested routes then treat current param as prefix and continue
        if (is_array($entry)) {
            $routing = $entry;
            return true;
        }

        // Set controller name
        $this->controllerName = $controller;

        // Check if controller is valid
        if (is_string($entry) && class_exists($entry)) {
            $this->controller = new $entry();
            if (!$this->controller instanceof ControllerInterface) {
                throw new HttpException("Controller class ({$entry}) must implement ControllerInterface");
            }
        } elseif (is_object($entry) && $entry instanceof ControllerInterface) {
            $this->controller = $entry;
        } else {
            throw new HttpException("Controller " . Formatter::toString($entry) . " at key {$param} is invalid");
        }

        return $this->controllerName !== self::DEFAULT_CONTROLLER_ROUTE_NAME;
    }

    /**
     * @param string $param
     * @return bool If param is consumed by this function
     * @throws HttpException
     */
    public function parseMethod(string $param): bool
    {
        $controller = $this->getController();
        $method = strtr($param, '-', '_');

        $controllerMethods = $controller instanceof Controller ? get_class_methods('Minwork\Basic\Controller\Controller') : get_class_methods('Minwork\Basic\Interfaces\ControllerInterface');

        // If method name is not internal, then use it
        if (!in_array($method, Framework::EVENTS) && !in_array($method, $controllerMethods) && is_callable([$controller, $method])) {
            $this->method = $method;
            return true;
        }

        if (method_exists($controller, self::DEFAULT_CONTROLLER_METHOD)) { // Otherwise use default method
            $this->method = self::DEFAULT_CONTROLLER_METHOD;
            return false;
        }

        throw new HttpException("Cannot find method {$method} inside {$this->getControllerName()} controller");
    }

    /**
     * Parse additional url param into method argument
     *
     * @param string $argument
     * @param bool $append
     */
    protected function addMethodArgument(string $argument, bool $append = true): void
    {
        if (strpos($argument, self::PARAMS_ARRAY_SEPARATOR) !== false) {
            $array = explode(self::PARAMS_ARRAY_SEPARATOR, $argument);
            $tmp = [];
            foreach ($array as $p) {
                $matches = [];
                if (preg_match(self::PARAMS_REGEX_ASSOC_PARAM, $p, $matches)) {
                    $tmp[$matches[1]] = $matches[2];
                } else {
                    $tmp[] = $p;
                }
            }
            $argument = $tmp;
        }
        if (is_string($argument) && preg_match(self::PARAMS_REGEX_ASSOC_PARAM, $argument, $matches)) {
            $this->methodArguments[$matches[1]] = $matches[2];
        } else {
            if ($append) {
                array_push($this->methodArguments, $argument);
            } else {
                array_unshift($this->methodArguments, $argument);
            }
        }
    }

    /**
     * @throws HttpException
     * @throws \ReflectionException
     */
    protected function normalizeMethodArguments(): void
    {
        // Set missing method arguments as null
        $reflection = new \ReflectionMethod($this->getController(), $this->getMethod());
        $numRequired = $reflection->getNumberOfRequiredParameters();
        $numCurrent = count($this->getMethodArguments());
        if ($numRequired > $numCurrent) {
            $this->methodArguments = array_merge($this->methodArguments, array_fill(count($this->methodArguments) - 1, $numRequired - $numCurrent, null));
        }
    }
}