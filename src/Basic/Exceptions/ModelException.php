<?php

namespace Minwork\Basic\Exceptions;

use Exception;

class ModelException extends Exception
{
    public static function notEmptyIdRequired()
    {
        return new self('Trying to use Model method which requires not empty id');
    }

    public static function unbindableModel()
    {
        return new self('Cannot bind model with multiple id fields');
    }
}