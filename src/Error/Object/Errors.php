<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Object;

use Minwork\Error\Interfaces\ErrorInterface;
use Minwork\Error\Interfaces\ErrorsStorageInterface;

/**
 * Basic class for error handling
 *
 * @author Christopher Kalkhoff
 *        
 */
class Errors implements ErrorsStorageInterface
{

    /**
     * List of error objects
     *
     * @var ErrorInterface[]
     */
    protected $list = [];

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorsStorageInterface::addError()
     */
    public function addError(ErrorInterface $error): ErrorsStorageInterface
    {
        $this->list[] = $error;
        
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorsStorageInterface::hasErrors()
     */
    public function hasErrors(): bool
    {
        return ! empty($this->list);
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
    public function merge(ErrorsStorageInterface $storage): ErrorsStorageInterface
    {
        foreach ($storage->getErrors() as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorsStorageInterface::getErrors()
     */
    public function getErrors($config = null): array
    {
        if (is_null($config)) {
            return $this->list;
        } elseif (is_string($config)) {
            // Filter errors by type
            return array_filter($this->list, function ($error) use ($config) {
                return $error->getType() === $config;
            });
        }
        return [];
    }
}