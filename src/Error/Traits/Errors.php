<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Traits;

use Minwork\Error\Basic\ErrorGlobal;
use Minwork\Error\Basic\ErrorForm;
use Minwork\Helper\Debugger;
use Minwork\Error\Object\Errors as ErrorsStorage;
use Minwork\Error\Interfaces\ErrorsStorageInterface;

/**
 * Trait for erros storage inside object
 * 
 * @author Christopher Kalkhoff
 *        
 */
trait Errors
{

    /**
     * Errors storage object
     * 
     * @var ErrorsStorageInterface
     */
    protected $errors = null;

    /**
     * Returns error object or creates it if necessary
     * 
     * @return ErrorsStorageInterface
     */
    public function getErrors(): ErrorsStorageInterface
    {
        if (is_null($this->errors)) {
            $this->errors = new ErrorsStorage();
        }
        return $this->errors;
    }

    /**
     * Add error using strings as arguments<br>
     * This method automatically creates fitting error object and adds it to storage
     * 
     * @param string ...$args            
     * @return self
     */
    public function addError(string ...$args): self
    {
        $count = count($args);
        
        if ($count < 1) {
            Debugger::debug('This method require at least one argument');
        }
        
        switch ($count) {
            case 1:
                $this->getErrors()->addError(new ErrorGlobal($args[0]));
                break;
            case 2:
                $this->getErrors()->addError(new ErrorForm($args[0], $args[1]));
                break;
            default:
                Debugger::debug("Invalid arguments count ({$count})");
                break;
        }
        return $this;
    }

    /**
     * If errors storage contain errors
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->getErrors()->hasErrors();
    }

    /**
     * Clear errors array
     * 
     * @return self
     */
    public function clearErrors(): self
    {
        $this->getErrors()->clearErrors();
        return $this;
    }
}