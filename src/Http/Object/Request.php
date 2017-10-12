<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Object;

use Minwork\Helper\Validation;
use Minwork\Helper\Formatter;
use Minwork\Http\Utility\cUrl;
use Minwork\Http\Utility\Server;
use Minwork\Http\Interfaces\RequestInterface;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Error\Traits\Errors;
use Minwork\Http\Utility\Url;
use Minwork\Basic\Traits\Debugger;

/**
 * Basic implementation of RequestInterface
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Request implements RequestInterface
{
    use Errors, Debugger;

    /**
     * Request method - default POST
     *
     * @var string
     */
    protected $method;

    /**
     * Query of the request - default $_GET array
     *
     * @var array
     */
    protected $query;

    /**
     * Request data - default $_POST array
     *
     * @var mixed
     */
    protected $body;

    /**
     * Request headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Requested URL - default current page address
     *
     * @var string|null
     */
    protected $url = null;

    /**
     * Create request object using query and request data
     *
     * @param array $query
     *            By default it should be data from $_GET
     * @param mixed $body
     *            By default it should be data from $_POST
     * @param array $headers
     *            Headers to send
     *            
     */
    public function __construct(array $query = [], $body = null, array $headers = [], string $method = cUrl::METHOD_POST): void
    {
        $this->setMethod($method)
            ->setQuery($query)
            ->setBody($body)
            ->setHeaders($headers);
    }

    /**
     * Create request object using $_GET and $_POST
     *
     * @return Request
     */
    public static function createFromGlobals(): self
    {
        $body = $_POST;
        // If body is empty try getting data from input
        if (empty($body)) {
            parse_str(file_get_contents('php://input'), $body);
        }
        return new self($_GET, $body, Server::getHeaders(), Server::getRequestMethod());
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RequestInterface::setUrl($url)
     */
    public function setUrl($url): RequestInterface
    {
        if (is_string($url)) {
            $this->url = (Validation::isUrl($url) ? $url : Formatter::makeUrl($url));
        } elseif (is_object($url) && $url instanceof Url) {
            $this->url = $url->getUrl();
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RequestInterface::execute($config)
     */
    public function execute($config = null): ResponseInterface
    {
        $url = new Url($this->getUrl());
        $curl = new cUrl($url->appendQuery($this->query), $this->body ?? []);
        $curl->setHeader($this->headers);
        $response = $curl->execute($this->getMethod());
        return $response;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\RequestInterface::getUrl()
     */
    public function getUrl(): string
    {
        if (is_null($this->url)) {
            $url = new Url(Server::getAbsoluteUrl());
            if (! empty($this->query)) {
                $url->appendQuery($this->query);
            }
            return $url->getUrl();
        }
        return $this->url;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::setMethod()
     */
    public function setMethod(string $method): RequestInterface
    {
        $this->method = $method;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::setQuery()
     */
    public function setQuery(array $query): RequestInterface
    {
        $this->query = $query;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::setBody()
     */
    public function setBody($body): RequestInterface
    {
        $this->body = $body;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::setHeaders()
     */
    public function setHeaders(array $headers): RequestInterface
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Append header (or replace if header with supplied name already exist) to request headers list
     * 
     * @param string $name
     *            Header name
     * @param string $value
     *            Header string value
     */
    public function appendHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::getMethod()
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::getQuery()
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::getBody()
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Http\Interfaces\RequestInterface::getHeaders()
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}