<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

/**
 * Interface for request object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface RequestInterface
{

    /**
     * Execute request to given url and return Response object
     *
     * @param mixed $config            
     * @return ResponseInterface
     */
    public function execute($config = null): ResponseInterface;

    /**
     * If request has any error
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Set requested url
     *
     * @see UrlInterface
     * @param string|object $url
     *            Either string with proper url or object implementing UrlInterface
     */
    public function setUrl($url): self;

    /**
     * Set request method
     *
     * @param string $method            
     * @return self
     */
    public function setMethod(string $method): self;

    /**
     * Set request query
     *
     * @param array $query            
     * @return self
     */
    public function setQuery(array $query): self;

    /**
     * Set request body
     *
     * @param mixed $body            
     * @return self
     */
    public function setBody($body): self;

    /**
     * Set request headers
     *
     * @param array $headers            
     * @return self
     */
    public function setHeaders(array $headers): self;

    /**
     * Get request errors
     *
     * @return mixed
     */
    public function getErrors();

    /**
     * Get current url
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get request query
     *
     * @return array
     */
    public function getQuery(): array;

    /**
     * Get request body
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Get request headers
     *
     * @return array
     */
    public function getHeaders(): array;
}