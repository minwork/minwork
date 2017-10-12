<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

use Minwork\Http\Interfaces\UrlInterface;
use Minwork\Basic\Traits\Debugger;

/**
 * Basic implementation of UrlInterface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Url implements UrlInterface
{
    use Debugger;

    const PARAM_SCHEME = "scheme";

    const PARAM_HOST = "host";

    const PARAM_PORT = "port";

    const PARAM_USER = "user";

    const PARAM_PASS = "pass";

    const PARAM_PATH = "path";

    const PARAM_QUERY = "query";

    const PARAM_FRAGMENT = "fragment";

    /**
     * String representation of url
     *
     * @var string
     */
    protected $rawUrl;

    /**
     * Array of url params
     *
     * @var array
     */
    protected $params;

    /**
     * Url query
     *
     * @var array
     */
    protected $query;

    /**
     *
     * @param string $url            
     */
    public function __construct(string $url): void
    {
        $this->rawUrl = $url;
        $this->params = $this->parseUrl($url);
    }

    /**
     * Get url param
     *
     * @param string $name
     *            Param name as specified in constants
     * @return string
     */
    protected function getUrlParam(string $name): string
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : '';
    }

    /**
     * Parse url string into array of params
     *
     * @param string $url            
     * @return array
     */
    protected function parseUrl(string $url): array
    {
        if (! empty($url) && ($parsedUrl = parse_url($url)) !== false) {
            $query = [];
            if (array_key_exists(self::PARAM_QUERY, $parsedUrl)) {
                parse_str($parsedUrl[self::PARAM_QUERY], $query);
            }
            $this->query = $query;
            
            return $parsedUrl;
        }
        $this->debug("Malformed URL {{$url})");
        return [];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\UrlInterface::getUrl()
     */
    public function getUrl(): string
    {
        $queryString = http_build_query($this->query);
        $scheme = $this->getUrlParam(self::PARAM_SCHEME);
        $host = $this->getUrlParam(self::PARAM_HOST);
        $port = $this->getUrlParam(self::PARAM_PORT);
        $user = $this->getUrlParam(self::PARAM_USER);
        $pass = $this->getUrlParam(self::PARAM_PASS);
        $path = $this->getUrlParam(self::PARAM_PATH);
        $query = $queryString;
        $fragment = $this->getUrlParam(self::PARAM_FRAGMENT);
        
        $userinfo = ! strlen($pass) ? $user : "$user:$pass";
        $host = ! "$port" ? $host : "$host:$port";
        $authority = ! strlen($userinfo) ? $host : "$userinfo@$host";
        $hier_part = ! strlen($authority) ? $path : "//$authority$path";
        $url = ! strlen($scheme) ? $hier_part : "$scheme:$hier_part";
        $url = ! strlen($query) ? $url : "$url?$query";
        $url = ! strlen($fragment) ? $url : "$url#$fragment";
        
        return $url;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\UrlInterface::appendQuery($query)
     */
    public function appendQuery($query): UrlInterface
    {
        $append = [];
        if (is_string($query)) {
            parse_str($query, $append);
        } elseif (is_array($query)) {
            $append = $query;
        }
        $this->query = array_merge($this->query, $append);
        
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\UrlInterface::getParam()
     */
    public function getParam(string $name): string
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }
        $this->debug("Param {$name} doesn't exists");
        return '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\UrlInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->buildUrl();
    }
}