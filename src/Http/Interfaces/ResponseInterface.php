<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

use Minwork\Http\Utility\HttpCode;

/**
 * Interface for response object
 * @author Christopher Kalkhoff
 *
 */
interface ResponseInterface
{
    const CONTENT_TYPE_JAVASCRIPT = 'text/javascript';
    
    const CONTENT_TYPE_PDF = 'application/pdf';
    
    const CONTENT_TYPE_RSS = 'application/rss+xml';
    
    const CONTENT_TYPE_TEXT = 'text/plain';
    
    const CONTENT_TYPE_XML = 'text/xml';
    
    const CONTENT_TYPE_HTML = 'text/html';
    
    const CONTENT_TYPE_JSON = 'application/json';
    
    const CONTENT_TYPE_CSS = 'text/css';
    
    /**
     * If response is empty
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Set response content
     * @param mixed $content
     * @return self
     */
    public function setContent($content): self;

    /**
     * Set response content type
     * @param string $type
     * @param bool $append
     * @return self
     */
    public function setContentType(string $type): self;

    /**
     * Add or replace headers array
     *
     * @param string|array $header
     * @param bool $append
     * @return self
     */
    public function setHeader($header, bool $append = true): self;

    /**
     * Set http code
     * @see HttpCode
     * @param int $code
     * @return self
     */
    public function setHttpCode(int $code): self;

    /**
     * Get response content
     * @return mixed
     */
    public function getContent();

    /**
     * Get response http code
     * @return int
     */
    public function getHttpCode(): int;

    /**
     * Get response headers
     * @return array
     */
    public function getHeaders(): array;
}