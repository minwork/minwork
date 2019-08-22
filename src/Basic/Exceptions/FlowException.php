<?php

namespace Minwork\Basic\Exceptions;

use Exception;
use Minwork\Http\Interfaces\ResponseInterface;

class FlowException extends Exception
{
    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface|null $response
     * @return FlowException
     */
    public function setResponse(?ResponseInterface $response): FlowException
    {
        $this->response = $response;
        return $this;
    }

    public static function breakFlow(?ResponseInterface $response = null)
    {
        return (new self('Controller flow stopped in one of pre method execution event'))->setResponse($response);
    }
}