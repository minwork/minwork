<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Object;

use Minwork\Helper\Formatter;
use Minwork\Http\Interfaces\RouterInterface;
use Minwork\Basic\Interfaces\ControllerInterface;
use Minwork\Http\Utility\LangCode;
use Minwork\Basic\Traits\Debugger;
use Minwork\Helper\ArrayHelper;
use Minwork\Core\Framework;

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
     * @var string
     */
    protected $lang;

    /**
     * Default language code if none is present in url
     *
     * @var string
     */
    protected $defaultLang;

    /**
     * Controller name
     *
     * @var string
     */
    protected $controller;

    /**
     * Controller object
     *
     * @var ControllerInterface
     */
    protected $controllerObject;

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
    protected $methodArguments;

    /**
     * Page number (by default 1)
     *
     * @var int
     */
    protected $page;

    /**
     * Array containing map of controller name to controller class or object
     *
     * @var array
     */
    protected $routing;

    /**
     *
     * @see \Minwork\Http\Object\Router::setRouting()
     * @param array|string $routing            
     */
    public function __construct($routing)
    {
        $this->reset()->setRouting(ArrayHelper::forceArray($routing));
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
        $this->controller = self::DEFAULT_CONTROLLER_ROUTE_NAME;
        $this->controllerObject = null;
        $this->method = self::DEFAULT_CONTROLLER_METHOD;
        $this->methodArguments = [];
        $this->page = 1;
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
                if (! array_key_exists(self::DEFAULT_CONTROLLER_ROUTE_NAME, $curRouting)) {
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
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getPage()
     */
    public function getPage(): int
    {
        return $this->page;
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
     */
    public function getController(): ControllerInterface
    {
        if (is_null($this->controllerObject)) {
            $controllerName = $this->controller;
            if (! array_key_exists($controllerName, $this->routing)) {
                // Try text id of controller name
                $controllerName = Formatter::textId($controllerName);
                if (! array_key_exists($controllerName, $this->routing)) {
                    $controllerName = self::DEFAULT_CONTROLLER_ROUTE_NAME;
                    if (! array_key_exists($controllerName, $this->routing)) {
                        throw new \DomainException("Cannot load controller object for key: {$this->controller}");
                    }
                    
                    $method = $this->method;
                    if ($method == self::DEFAULT_CONTROLLER_METHOD) {
                        $this->addMethodArgument($this->methodArguments, $this->controller, false);
                    } else {
                        $this->addMethodArgument($this->methodArguments, $method, false);
                        $this->method = $this->controller;
                    }
                    $this->controller = $controllerName;
                }
            }
            $controllerClass = $this->routing[$controllerName];
            
            if (is_string($controllerClass) && class_exists($controllerClass)) {
                $this->controllerObject = new $controllerClass();
            } elseif (is_object($controllerClass) && $controllerClass instanceof ControllerInterface) {
                $this->controllerObject = $controllerClass;
            } else {
                throw new \DomainException("Controller class ({$controllerClass}) is invalid");
            }
        }
        return $this->controllerObject;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RouterInterface::getControllerName()
     */
    public function getControllerName(): string
    {
        return $this->controller;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RouterInterface::getMethod()
     */
    public function getMethod(): string
    {
        return $this->method;
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
     * @param bool $sanitaze
     *            If extracted params should be sanitized
     * @return self
     */
    public function translateUrl(string $url, bool $sanitize = true): RouterInterface
    {
        $return = [];
        $this->url = $url;
        
        $routeUrl = $sanitize ? Formatter::removeTrailingSlash(Formatter::removeLeadingSlash($url)) : $url;
        $params = empty($routeUrl) ? [] : explode("/", $routeUrl);
        $params = $sanitize ? Formatter::cleanData($params) : $params;
        if ($params !== false && count($params) != 0) {
            foreach ($params as $param) {
                $matches = [];
                if ((preg_match(self::PARAMS_REGEX_PAGE, $param, $matches))) { // Page
                    $page = intval($matches[1]);
                    $return['page'] = $page > 0 ? $page : 1;
                } elseif ((preg_match(self::PARAMS_REGEX_LANG, $param, $matches)) && in_array($matches[1], LangCode::CODES_LIST)) { // Language
                    $return['lang'] = $matches[1];
                } elseif (! isset($return['controller'])) { // Module
                    $return['controller'] = (string) $param;
                } elseif (! isset($return['method'])) { // Method
                    $return['method'] = $param;
                } elseif ($param !== '') { // Method argument
                    if (! array_key_exists('methodArguments', $return)) {
                        $return['methodArguments'] = [];
                    }
                    $this->addMethodArgument($return['methodArguments'], $param);
                }
            }
        }
        
        foreach ($return as $paramName => $paramValue) {
            $this->$paramName = $paramValue;
        }
        
        $controller = $this->getController();
        $method = $this->getMethod();
        $methodNormalized = strtr($method, '-', '_');
        
        // Process method name
        if (! in_array($methodNormalized, Framework::EVENTS) && method_exists($controller, $methodNormalized)) {
            $this->method = $methodNormalized;
        } elseif (method_exists($controller, self::DEFAULT_CONTROLLER_METHOD)) {
            $this->addMethodArgument($this->methodArguments, $method);
            $this->method = self::DEFAULT_CONTROLLER_METHOD;
        } else {
            throw new \DomainException("Cannot find method {$methodNormalized} inside " . get_class($controller) . " controller");
        }
        
        // Set missing method arguments as null
        $reflection = new \ReflectionMethod($this->getController(), $this->getMethod());
        $numRequired = $reflection->getNumberOfRequiredParameters();
        $numCurrent = count($this->getMethodArguments());
        if ($numRequired > $numCurrent) {
            $this->methodArguments = array_merge($this->methodArguments, array_fill(count($this->methodArguments) - 1, $numRequired - $numCurrent, null));
        }
        
        return $this;
    }

    /**
     * Parse additional url param into method argument
     *
     * @param array $currentArguments            
     * @param string $newArgument            
     * @param bool $append            
     * @return self
     */
    private function addMethodArgument(array &$currentArguments, string $newArgument, bool $append = true): self
    {
        if (strpos($newArgument, self::PARAMS_ARRAY_SEPARATOR) !== false) {
            $array = explode(self::PARAMS_ARRAY_SEPARATOR, $newArgument);
            $tmp = [];
            foreach ($array as $p) {
                $matches = [];
                if (preg_match(self::PARAMS_REGEX_ASSOC_PARAM, $p, $matches)) {
                    $tmp[$matches[1]] = $matches[2];
                } else {
                    $tmp[] = $p;
                }
            }
            $newArgument = $tmp;
        }
        if (is_string($newArgument) && preg_match(self::PARAMS_REGEX_ASSOC_PARAM, $newArgument, $matches)) {
            $currentArguments[$matches[1]] = $matches[2];
        } else {
            if ($append) {
                array_push($currentArguments, $newArgument);
            } else {
                array_unshift($currentArguments, $newArgument);
            }
        }
        
        return $this;
    }
}