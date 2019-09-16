<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Utility;

use Minwork\Helper\Formatter;
use Minwork\Http\Interfaces\EnvironmentInterface;

/**
 * Stores environmental variables
 *
 * @author Christopher Kalkhoff
 *        
 */
class Environment implements EnvironmentInterface
{

    /**
     * Application domain (like https://mydomain.com or http://subdomain.otherdomain.com:3000/test)
     *
     * @var string
     */
    protected $domain;

    /**
     * Environment type (development, production, local)
     *
     * @var string
     */
    protected $type;

    public function __construct(string $domain = '', string $type = EnvironmentInterface::TYPE_DEVELOPMENT)
    {
        $this->setDomain(empty($domain) ? Server::getDomain() : $domain)->setType($type);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\EnvironmentInterface::getType()
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\EnvironmentInterface::setType()
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
     * @see \Minwork\Http\Interfaces\EnvironmentInterface::getDomain()
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Http\Interfaces\EnvironmentInterface::setDomain()
     */
    public function setDomain(string $domain): EnvironmentInterface
    {
        $this->domain = Formatter::makeUrl($domain);
        return $this;
    }
}