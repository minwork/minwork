<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Object;

use Minwork\Error\Interfaces\ErrorInterface;

/**
 * Prototype of error object
 *
 * @author Christopher Kalkhoff
 *        
 */
abstract class ErrorPrototype implements ErrorInterface
{

    /**
     * Message
     *
     * @var string
     */
    protected $message;

    /**
     * Field name associated with error
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Create error with string message
     * 
     * @param string $message            
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getType()
     */
    public function getType(): string
    {
        return '';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getRawMessage()
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getHTML()
     */
    public function __toString(): string
    {
        return $this->getMessage();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::hasFieldName()
     */
    public function hasFieldName(): bool
    {
        return ! is_null($this->fieldName);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::setFieldName()
     */
    public function setFieldName(string $name): ErrorInterface
    {
        $this->fieldName = $name;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getFieldName()
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}