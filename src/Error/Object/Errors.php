<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Object;

use Minwork\Error\Interfaces\ErrorInterface;
use Minwork\Helper\ArrayHelper;
use Minwork\Error\Interfaces\ErrorsStorageInterface;
use Minwork\Error\Object\ErrorPrototype;

/**
 * Basic class for error handling
 *
 * @author Christopher Kalkhoff
 *        
 */
class Errors implements ErrorsStorageInterface
{

    /**
     * Storage for errors
     *
     * @var array
     */
    protected $list = [];

    /**
     * Add error to list
     *
     * @param ErrorPrototype $errorObj            
     * @return Error
     */
    public function addError(ErrorInterface $errorObj): ErrorsStorageInterface
    {
        $type = $errorObj->getType();
        
        if (! array_key_exists($type, $this->list)) {
            $this->list[$type] = [];
        }
        
        if ($errorObj->hasFieldName()) {
            $this->list[$type][$errorObj->getFieldName()] = $errorObj;
        } else {
            $this->list[$type][] = $errorObj;
        }
        
        return $this;
    }

    /**
     * Create object, add error and return
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param ErrorPrototype $errorObj            
     * @return Error
     */
    public static function addAndReturn(ErrorInterface $errorObj)
    {
        $error = new self();
        $error->add($errorObj);
        return $error;
    }

    /**
     * Check if there were any errors added
     *
     * @return boolean
     */
    public function hasErrors(): bool
    {
        return ! ArrayHelper::isEmpty($this->list);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorsStorageInterface::clearErrors()
     */
    public function clearErrors(): ErrorsStorageInterface
    {
        $this->list = [];
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorsStorageInterface::merge()
     */
    public function merge(ErrorsStorageInterface $error): ErrorsStorageInterface
    {
        $errors = $error->getErrors();
        foreach ($errors as $list) {
            foreach ($list as $error) {
                $this->addError($error);
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \MinWork\Error\Interfaces\ErrorsContainerInterface::getErrors()
     */
    public function getErrors($config = null): array
    {
        $return = [];
        if (is_null($config)) {
            $return = $this->list;
            ;
        } elseif (array_key_exists($config, $this->list)) {
            $return = $this->list[$config];
        }
        return $return;
    }
}