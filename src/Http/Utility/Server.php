<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Minwork\Http\Utility;

/**
 * Helper class for obtaining informations stored in $_SERVER array<br>
 * Returns default value if specified option is not present in $_SERVER array
 *
 * @author Christopher Kalkhoff
 *
 */
class Server
{

    const HTTPS_DISABLED = "off";

    const HTTPS_PORT = '443';

    const HTTPS_PROTOCOL = 'https';

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
        return $_SERVER['HTTPS'] ?? self::HTTPS_DISABLED;
    }

    /**
     * Get server name
     *
     * @return string
     */
    public static function getServerName(): string
    {
        return $_SERVER['SERVER_NAME'] ?? self::DEFAULT_SERVER_NAME;
    }

    /**
     * Get server port
     *
     * @return string
     */
    public static function getPort(): string
    {
        return $_SERVER['SERVER_PORT'] ?? self::DEFAULT_PORT;
    }

    /**
     * Get server protocol
     *
     * @return string
     */
    public static function getProtocol(): string
    {
        return $_SERVER['SERVER_PROTOCOL'] ?? self::DEFAULT_PROTOCOL;
    }

    /**
     * Get relative URL (without domain) of current page (like 'test/test')
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
     * Get request http method
     *
     * @return string
     */
    public static function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? self::DEFAULT_METHOD;
    }

    /**
     * Get requester IP address
     *
     * @return string
     */
    public static function getClientIP(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? self::DEFAULT_REMOTE_ADDR;
    }

    /**
     * Get relative URL of page
     *
     * @return string
     */
    public static function getRelativeUrl(): string
    {
        return array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '/';
    }

    /**
     * Get currently used protocol name (like http or https)
     *
     * @return string
     */
    public static function getProtocolName(): string
    {
        if (self::isSecure()) {
            return self::HTTPS_PROTOCOL;
        }
        return strtolower(substr(self::getProtocol(), 0, strpos(self::getProtocol(), '/')));
    }

    public static function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * If request is using SSL
     *
     * @return bool
     */
    public static function isSecure(): bool
    {
        return
            (!empty(self::getHttps()) && self::getHttps() !== self::HTTPS_DISABLED) ||
            self::getPort() == self::HTTPS_PORT ||
            // Case for SSL gateway
            ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? '') == self::HTTPS_PORT ||
            ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') == self::HTTPS_PROTOCOL;
    }

    /**
     * Get current page domain
     * <pre>protocol://server_name[:port]</pre>
     *
     * @return string
     */
    public static function getDomain(): string
    {
        $pageUrl = self::getProtocolName() . "://";

        // If not using default port then append specific port
        if (!self::isDefaultPort()) {
            $pageUrl .= self::getServerName() . ":" . self::getPort();
        } else {
            $pageUrl .= self::getServerName();
        }
        return $pageUrl;
    }

    /**
     * Get absolute URL of current page
     * <pre>protocol::/server_name[:port]/[relative_url]</pre>
     *
     * @return string
     */
    public static function getAbsoluteUrl(): string
    {
        return self::getDomain() . self::getRelativeUrl();
    }

    /**
     * Get aproximated request process time or 0 if cannot get it from $_SERVER array
     *
     * @return float
     */
    public static function getTimer(): float
    {
        return array_key_exists('REQUEST_TIME_FLOAT', $_SERVER) ? microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] : 0;
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
     * If server use default port for it's protocol (80 for http, 443 for https)
     *
     * @return bool
     */
    public static function isDefaultPort(): bool
    {
        $port = self::getPort();
        return (self::isSecure() && $port === self::HTTPS_PORT) || (!self::isSecure() && $port === self::DEFAULT_PORT);
    }

    /**
     * Get server request headers
     *
     * @return array
     */
    public static function getHeaders(): array
    {
        if (!function_exists('getallheaders')) {
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
            /** @noinspection PhpComposerExtensionStubsInspection */
            return getallheaders();
        }
    }
}