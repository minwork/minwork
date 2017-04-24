<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

use Minwork\Http\Interfaces\EnvironmentInterface;
use Minwork\Http\Utility\Server;

/**
 * Stores environmental variables
 * 
 * @author Christopher Kalkhoff
 *        
 */
class Environment implements EnvironmentInterface
{
    protected $domain;
    protected $type;
    public function __construct(string $domain = null, string $type = EnvironmentInterface::TYPE_DEVELOPMENT)
    {
        $this->domain = $domain ?? Server::getDomain();
        $this->type = $type;
    }
    
    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\EnvironmentInterface::getType()
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\EnvironmentInterface::setType()
     */
    public function setType(string $type): EnvironmentInterface
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\EnvironmentInterface::getDomain()
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Basic\Interfaces\EnvironmentInterface::setDomain()
     */
    public function setDomain(string $domain): EnvironmentInterface
    {
        $this->domain = $domain;
        return $this;
    }
}