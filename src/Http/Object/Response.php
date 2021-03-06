<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Object;

use Minwork\Http\Utility\HttpCode;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Basic\Interfaces\ViewInterface;

/**
 * Basic implementation of ResponseInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Response implements ResponseInterface
{

    /**
     * If response is empty
     *
     * @var bool
     */
    protected $empty = true;

    /**
     * Object with response content
     *
     * @var ViewInterface
     */
    protected $object;

    /**
     * String representation of response content
     *
     * @var string
     */
    protected $content;

    /**
     * Http code
     *
     * @var int
     */
    protected $httpCode;

    /**
     * List of headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     *
     * @param mixed $content            
     * @param string $contentType            
     * @param int $httpCode            
     */
    public function __construct($content = null, string $contentType = self::CONTENT_TYPE_HTML, int $httpCode = HttpCode::OK)
    {
        $this->setContent($content)
            ->setContentType($contentType)
            ->setHttpCode($httpCode);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::isEmpty()
     */
    public function isEmpty(): bool
    {
        return $this->empty;
    }

    /**
     * Set object with content for response
     *
     * @param ViewInterface $obj            
     * @return self
     */
    public function setObject(ViewInterface $obj): self
    {
        $this->object = $obj;
        $this->setContent($obj->getContent())
            ->setContentType($obj->getContentType());
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::setContentType($type)
     */
    public function setContentType(string $type): ResponseInterface
    {
        $contentType = "Content-Type: {$type}";
        $matches = array_filter($this->headers, function ($element) {
            return strpos($element, "Content-Type") !== false;
        });
        // Replace existing content type header with new one
        foreach (array_keys($matches) as $key) {
            unset($this->headers[$key]);
        }
        array_unshift($this->headers, $contentType);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader(string $name, $value = null, bool $caseInsensitive = true): ResponseInterface
    {
        if (is_null($value)) {
            $this->headers[] = $name;
        } else {
            if ($caseInsensitive) {
                $nameLc = strtolower($name);
                foreach (array_keys($this->headers) as $key) {
                    if (strtolower($key) === $nameLc) {
                        unset($this->headers[$key]);
                    }
                }
            }
            $this->headers[$name] = $value;
        }
        $this->empty = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers): ResponseInterface
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeHeader(string $name, bool $caseInsensitive = true): ResponseInterface
    {
        if ($caseInsensitive) {
            $nameLc = strtolower($name);
            foreach (array_keys($this->headers) as $key) {
                if (strtolower($key) === $nameLc) {
                    unset($this->headers[$key]);
                }
            }
        } else {
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearHeaders(): ResponseInterface
    {
        $this->headers = [];

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::setContent($content)
     */
    public function setContent($content): ResponseInterface
    {
        $this->empty = is_null($content);
        $this->content = $content;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::getContent()
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::setHttpCode($code)
     */
    public function setHttpCode(int $code): ResponseInterface
    {
        if ($code !== HttpCode::OK) {
            $this->empty = false;
        }
        $this->httpCode = $code;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::getHttpCode()
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::getHeaders()
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Create response object based on variable value
     *
     * @param $var
     * @return Response
     */
    public static function createFrom($var): self
    {
        if (is_object($var) && $var instanceof ViewInterface) {
            return (new self())->setObject($var);
        }

        return new self($var);
    }
}