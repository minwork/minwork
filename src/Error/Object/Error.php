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
class Error implements ErrorInterface, \JsonSerializable
{
    const TYPE = 'general';

    /**
     * Message
     *
     * @var string
     */
    protected $message;

    /**
     * Reference to something this error concerns
     *
     * @var mixed
     */
    protected $ref = null;

    /**
     * List of additional error data
     *
     * @var array
     */
    protected $data;

    /**
     * Create error with string message
     *
     * @param string $message
     * @param array $data
     */
    public function __construct(string $message, ...$data)
    {
        $this->setMessage($message)->setData($data);
    }

    /**
     * @param string $message
     * @return Error
     */
    protected function setMessage(string $message): ErrorInterface
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param array $data
     * @return Error
     */
    protected function setData(array $data): ErrorInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getType()
     */
    public function getType(): string
    {
        return static::TYPE;
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
     * Specify data which should be serialized to JSON
     */
    public function jsonSerialize()
    {
        return $this->getMessage();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::hasRef()
     */
    public function hasRef(): bool
    {
        return ! is_null($this->ref);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::setRef()
     * @return Error
     */
    public function setRef($ref): ErrorInterface
    {
        $this->ref = $ref;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Interfaces\ErrorInterface::getRef()
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Get list of additional error data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}