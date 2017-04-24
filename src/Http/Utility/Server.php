<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

/**
 * Helper for informations stored in $_SERVER array<br>
 * Returns default value if specified key is not present in $_SERVER
 *
 * @author Christopher Kalkhoff
 *        
 */
class Server
{

    const HTTPS_DISABLED = "off";

    const HTTPS_PORT = '443';

    const DEFAULT_PORT = '80';

    const DEFAULT_SERVER_NAME = 'localhost';

    const DEFAULT_PROTOCOL = 'HTTP/1.1';

    const DEFAULT_REMOTE_ADDR = '127.0.0.1';

    const DEFAULT_METHOD = cUrl::METHOD_GET;

    /**
     * If script was queried through the HTTPS protocol
     * 
     * @return string
     */
    public static function getHttps(): string
    {
        return array_key_exists('HTTPS', $_SERVER) ? $_SERVER['HTTPS'] : self::HTTPS_DISABLED;
    }

    /**
     * Get server name
     * 
     * @return string
     */
    public static function getServerName(): string
    {
        return array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : self::DEFAULT_SERVER_NAME;
    }

    /**
     * Get server port
     * 
     * @return string
     */
    public static function getPort(): string
    {
        return array_key_exists('SERVER_PORT', $_SERVER) ? $_SERVER['SERVER_PORT'] : self::DEFAULT_PORT;
    }

    /**
     * Get server protocol
     * 
     * @return string
     */
    public static function getProtocol(): string
    {
        return array_key_exists('SERVER_PROTOCOL', $_SERVER) ? $_SERVER['SERVER_PROTOCOL'] : self::DEFAULT_PROTOCOL;
    }

    /**
     * Get relative URL of page
     *
     * @return string
     */
    public static function getDocumentRoot(): string
    {
        if (array_key_exists('DOCUMENT_ROOT', $_SERVER)) {
            return $_SERVER['DOCUMENT_ROOT'];
        } else {
            $dirArray = explode(DIRECTORY_SEPARATOR, __DIR__);
            return implode(DIRECTORY_SEPARATOR, array_slice($dirArray, 0, count($dirArray) - 3));
        }
    }

    /**
     * Get request method
     *
     * @return string
     */
    public static function getRequestMethod(): string
    {
        return array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : self::DEFAULT_METHOD;
    }

    /**
     * Get requester IP address
     *
     * @return string
     */
    public static function getClientIP(): string
    {
        return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : self::DEFAULT_REMOTE_ADDR;
    }

    /**
     * Get relative URL of page
     *
     * @return string
     */
    public static function getRelativeURL(): string
    {
        return array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '/';
        ;
    }

    /**
     * Get currently used protocol
     *
     * @return string
     */
    public static function getProtocolName(): string
    {
        if (self::isSecure()) {
            return 'https';
        }
        return strtolower(substr(self::getProtocol(), 0, strpos(self::getProtocol(), '/')));
    }

    /**
     * If request is using SSL
     *
     * @return bool
     */
    public static function isSecure(): bool
    {
        return (! empty(self::getHttps()) && self::getHttps() !== self::HTTPS_DISABLED) || self::getPort() == self::HTTPS_PORT;
    }

    /**
     * Get absolute URL of current page
     *
     * @return string
     */
    public static function getDomain(): string
    {
        $pageUrl = self::getProtocol() . "://";
        
        if (self::getPort() != self::DEFAULT_PORT && ! self::isSecure()) {
            $pageUrl .= self::getServerName() . ":" . self::getPort();
        } else {
            $pageUrl .= self::getServerName();
        }
        return $pageUrl;
    }

    /**
     * Request absolute URL
     *
     * @return string
     */
    public static function getAbsoluteURL(): string
    {
        return self::getDomain() . self::getRelativeURL();
    }

    /**
     * Get aproximated request process time
     *
     * @return float
     */
    public static function getTimer(): float
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * If request was made using ajax
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        return array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Get server request headers
     *
     * @return array
     */
    public static function getHeaders(): array
    {
        if (! function_exists('getallheaders')) {
            $headers = [];
            if (is_array($_SERVER)) {
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
            }
            return $headers;
        } else {
            return getallheaders();
        }
    }
}