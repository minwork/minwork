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
use Minwork\Error\Object\Errors as ErrorsStorage;
use Minwork\Error\Interfaces\ErrorsStorageInterface;

/**
 * Trait used for adding, getting and clearing errors using basic storage that implements ErrorsStorageInterface
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
     * Create error object based on arguments and add it object to errors storage
     * By default it creates ErrorGlobal object
     *
     * @see \Minwork\Error\Basic\ErrorForm
     * @see \Minwork\Error\Basic\ErrorGlobal
     * @param string $args,...
     *            Error properties used for creating appropiate object, like:<br>
     *            <i>addError(field_name, message)</i> will create ErrorForm<br>
     *            <i>addError(message)</i> will create ErrorGlobal
     *            
     * @return self
     */
    public function addError(string ...$args): self
    {
        $count = count($args);
        
        switch ($count) {
            case 2:
                $this->getErrors()->addError(new ErrorForm($args[0], $args[1]));
                break;
            case 1:
            default:
                $this->getErrors()->addError(new ErrorGlobal($args[0]));
                break;
        }
        return $this;
    }

    /**
     * If errors storage contain any error
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->getErrors()->hasErrors();
    }

    /**
     * Clear stored errors
     *
     * @return self
     */
    public function clearErrors(): self
    {
        $this->getErrors()->clearErrors();
        return $this;
    }
}