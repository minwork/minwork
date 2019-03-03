<?php


namespace Minwork\Error\Basic;


use Minwork\Error\Object\Error;

class FieldError extends Error
{
    const TYPE = 'field';

    /**
     * FieldError constructor.
     * @param string $name
     * @param string $message
     */
    public function __construct(string $name, string $message, ...$data)
    {
        parent::__construct($message, ...$data);
        $this->setRef($name);
    }
}