<?php


namespace Minwork\Basic\Utility;


use Minwork\Basic\Exceptions\FlowException;
use Minwork\Event\Object\Event;
use Minwork\Http\Interfaces\ResponseInterface;

class FlowEvent extends Event
{
    /**
     * @param ResponseInterface|null $response
     * @throws FlowException
     */
    public function breakFlow(?ResponseInterface $response = null): void
    {
        throw FlowException::breakFlow($response);
    }
}