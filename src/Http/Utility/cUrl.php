<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

use Minwork\Http\Object\Response;
use Minwork\Http\Interfaces\UrlInterface;
use Minwork\Http\Interfaces\ResponseInterface;
use Minwork\Basic\Traits\Debugger;

/**
 * Basic cUrl handler
 *
 * @author Christopher Kalkhoff
 *        
 */
class cUrl
{
    use Debugger;

    const METHOD_GET = "GET";

    const METHOD_POST = "POST";

    const METHOD_PATCH = "PATCH";

    const METHOD_PUT = "PUT";

    const METHOD_DELETE = "DELETE";

    /**
     * Url object
     * 
     * @var UrlInterface
     */
    protected $url;

    /**
     * Used as POST body fields or GET query
     * 
     * @var array
     */
    protected $fields;

    /**
     * cURL handle
     * 
     * @var resource
     */
    protected $curl;

    /**
     * cURL cookies set by CURLOPT_COOKIE option
     * 
     * @var array
     */
    protected $cookies = [];

    /**
     * cURL http headers set by CURLOPT_HTTPHEADER option
     * 
     * @var array
     */
    protected $headers = [];

    /**
     * cURL options list used for curl_setopt
     * 
     * @var array
     */
    protected $options = [];

    /**
     *
     * @param UrlInterface $url            
     * @param array $fields            
     */
    public function __construct(UrlInterface $url, array $fields = []): void
    {
        if (! extension_loaded("curl")) {
            throw new \Exception("You must enable cURL library first");
        }
        $this->setUrl($url)->setFields($fields);
    }

    /**
     * Set cURL fields used as POST body fields or GET query
     *
     * @param array $fields            
     * @return self
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set url object
     *
     * @param UrlInterface $url            
     * @return self
     */
    public function setUrl(UrlInterface $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set curl option used for curl_setopt
     *
     * @param string $option            
     * @param mixed $value            
     * @return self
     */
    public function setOpt(string $option, $value): self
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Get url object
     *
     * @return UrlInterface
     */
    public function getUrl(): UrlInterface
    {
        return $this->url;
    }

    /**
     * Execute curl request
     *
     * @see self::handleResponse()
     * @param string $method            
     * @return ResponseInterface
     */
    public function execute(string $method = self::METHOD_POST): ResponseInterface
    {
        $this->curl = curl_init();
        
        $this->init();
        
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->debug("Method not found ({$method})");
        }
        
        // Set all options
        foreach ($this->options as $curlopt => $val) {
            curl_setopt($this->curl, $curlopt, $val);
        }
        
        $response = $this->handleResponse(curl_exec($this->curl));
        
        curl_close($this->curl);
        
        return $response;
    }

    /**
     * Handle curl response and parse it to Response object
     *
     * @param string $response            
     * @return ResponseInterface
     */
    protected function handleResponse($response): ResponseInterface
    {
        if (($error = curl_error($this->curl)) !== '') {
            $response = $error;
        }
        return new Response($response, curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE), curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
    }

    /**
     * Reset curl options
     *
     * @return self
     */
    protected function init(): self
    {
        $this->setOpt(CURLINFO_HEADER_OUT, false);
        $this->setOpt(CURLOPT_HEADER, false);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        
        // Prepare headers
        if (! empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $name => $value) {
                $headers[] = "{$name}: {$value}";
            }
            $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        }
        return $this;
    }

    /**
     * Prepare GET method options
     *
     * @return self
     */
    protected function get(): self
    {
        if (! empty($this->fields)) {
            $this->setOpt(CURLOPT_URL, $this->url->appendQuery($this->fields)
                ->getUrl());
        } else {
            $this->setOpt(CURLOPT_URL, $this->url->getUrl());
        }
        $this->setOpt(CURLOPT_HTTPGET, true);
        
        return $this;
    }

    /**
     * Prepare POST method options
     *
     * @return self
     */
    protected function post(): self
    {
        $this->setOpt(CURLOPT_URL, $this->url->getUrl())
            ->setOpt(CURLOPT_POST, true)
            ->setOpt(CURLOPT_POSTFIELDS, http_build_query($this->fields));
        
        return $this;
    }

    /**
     * Prepare PUT method options
     *
     * @return self
     */
    protected function put(): self
    {
        $this->setOpt(CURLOPT_URL, $this->url->appendQuery($this->fields));
        $this->setOpt(CURLOPT_CUSTOMREQUEST, self::METHOD_PUT);
        
        return $this;
    }

    /**
     * Prepare PATCH method options
     *
     * @return self
     */
    protected function patch(): self
    {
        $this->setOpt(CURLOPT_URL, $this->url->getUrl())
            ->setOpt(CURLOPT_POST, true)
            ->setOpt(CURLOPT_POSTFIELDS, http_build_query($this->fields))
            ->setOpt(CURLOPT_CUSTOMREQUEST, self::METHOD_PATCH);
        
        return $this;
    }

    /**
     * Prepare DELETE method options
     *
     * @return self
     */
    protected function delete(): self
    {
        $this->setOpt(CURLOPT_URL, $this->url->appendQuery($this->fields)
            ->getUrl());
        $this->setOpt(CURLOPT_CUSTOMREQUEST, self::METHOD_DELETE);
        
        return $this;
    }

    /**
     * Set curl basic authentication
     *
     * @param string $username            
     * @param string $password            
     * @return self
     */
    public function setBasicAuth(string $username, string $password): self
    {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    /**
     * Set headers
     *
     * @param string|array $header            
     * @param bool $merge            
     * @return self
     */
    public function setHeader($header, bool $merge = true): self
    {
        if (is_string($header)) {
            if ($merge) {
                $this->headers[] = $header;
            } else {
                $this->headers = [
                    $header
                ];
            }
        } elseif (is_array($header)) {
            $this->headers = ($merge) ? array_merge($this->headers, $header) : $header;
        } else {
            $this->debug("Invalid header format");
        }
        return $this;
    }

    /**
     * Set curl user agent option
     *
     * @param string $agent            
     * @return self
     */
    public function setUserAgent(string $agent): self
    {
        $this->setOpt(CURLOPT_USERAGENT, $agent);
        return $this;
    }

    /**
     * Set curl referrer option
     *
     * @param string $referrer            
     * @return self
     */
    public function setReferrer(string $referrer): self
    {
        $this->setOpt(CURLOPT_REFERER, $referrer);
        return $this;
    }

    /**
     * Set curl cookie option
     *
     * @param string $key            
     * @param mixed $value            
     * @return self
     */
    public function setCookie(string $key, $value): self
    {
        $this->cookies[$key] = $value;
        $this->setOpt(CURLOPT_COOKIE, http_build_query($this->cookies, '', '; '));
        return $this;
    }
}