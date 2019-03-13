<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Traits;

use Minwork\Error\Basic\FieldError;
use Minwork\Error\Interfaces\ErrorInterface;
use Minwork\Error\Interfaces\ErrorsStorageInterface;
use Minwork\Error\Object\Error;
use Minwork\Error\Object\Errors as ErrorsStorage;

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
     * Return errors storage object (creates it if necessary)
     *
     * @return ErrorsStorageInterface
     */
    public function getErrorsStorage(): ErrorsStorageInterface
    {
        if (is_null($this->errors)) {
            $this->setErrorsStorage(new ErrorsStorage());
        }
        return $this->errors;
    }

    /**
     * Set errors storage object
     *
     * @param ErrorsStorageInterface $errors
     * @return Errors
     */
    public function setErrorsStorage(ErrorsStorageInterface $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Create error object based on arguments and add it object to errors storage
     * By default it creates Error object
     *
     * @see \Minwork\Error\Basic\FieldError
     * @see \Minwork\Error\Object\Error
     * @param mixed ...$args
     *            Error properties used for creating appropriate object, like:<br>
     *            <i>addError(field_name, message)</i> will create FieldError<br>
     *            <i>addError(message)</i> will create regular Error
     *            
     * @return self
     */
    public function addError(...$args): self
    {
        $count = count($args);
        
        switch ($count) {
            case 2:
                $this->getErrorsStorage()->addError(new FieldError(...$args));
                break;
            case 1:
                if ($args[0] instanceof ErrorInterface) {
                    $this->getErrorsStorage()->addError($args[0]);
                } else {
                    $this->getErrorsStorage()->addError(new Error(...$args));
                }
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
        return $this->getErrorsStorage()->hasErrors();
    }

    /**
     * Clear stored errors
     *
     * @return self
     */
    public function clearErrors(): self
    {
        $this->getErrorsStorage()->clearErrors();
        return $this;
    }
}