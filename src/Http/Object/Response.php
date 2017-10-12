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
    public function __construct($content = '', string $contentType = self::CONTENT_TYPE_TEXT, int $httpCode = HttpCode::OK)
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
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\ResponseInterface::setHeader($header, $append)
     */
    public function setHeader($header, bool $merge = true): ResponseInterface
    {
        if ($merge) {
            if (is_string($header)) {
                $this->headers[] = $header;
            } elseif (is_array($header)) {
                $this->headers = array_merge($this->headers, $header);
            }
        } else {
            if (is_string($header)) {
                $header = [
                    $header
                ];
            }
            if (is_array($header)) {
                $this->headers = $header;
            }
        }
        $this->empty = false;
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
        $this->empty = empty($content);
        $this->content = strval($content);
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
}