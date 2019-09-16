<?php

namespace Minwork\Basic\Exceptions;

use Exception;

class ModelException extends Exception
{
    public static function missingOperationInterface(string $interfaceName)
    {
        return new self("Operations trait target doesn't implement '{$interfaceName}' interface to use method which requires it");
    }
}