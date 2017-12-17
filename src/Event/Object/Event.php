<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Event\Object;

use Minwork\Event\Interfaces\EventInterface;

/**
 * Basic implementation of event interface
 *
 * @author Christopher Kalkhoff
 *        
 */
class Event implements EventInterface
{

    /**
     * If event is active
     *
     * @var bool
     */
    protected $active;

    /**
     * Event data that will be accessible by listener
     *
     * @var array
     */
    protected $data;

    /**
     * Event name
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @param string $name
     * @param mixed $data
     */
    public function __construct(string $name, ...$data)
    {
        $this->setData(...$data)->setActive(true)->name = $name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventInterface::__toString()
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Get event data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set event data
     *
     * @param mixed $data
     * @return self
     */
    public function setData(...$data): EventInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventInterface::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventInterface::setActive($active)
     */
    public function setActive(bool $active): EventInterface
    {
        $this->active = $active;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Event\Interfaces\EventInterface::isActive()
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}