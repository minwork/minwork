<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

/**
 * Interface for environment object
 *
 * @author Christopher Kalkhoff
 *        
 */
interface EnvironmentInterface
{

    const TYPE_LOCAL = 'local';

    const TYPE_DEVELOPMENT = 'develop';

    const TYPE_PRODUCTION = 'master';

    /**
     * Get environment type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set environment type
     *
     * @param string $type            
     * @return EnvironmentInterface
     */
    public function setType(string $type): EnvironmentInterface;

    /**
     * Get environment domain address
     *
     * @return string
     */
    public function getDomain(): string;

    /**
     * Set environment domain address trailing slash (like https://mydomain.com or http://subdomain.otherdomain.com:3000/test)
     *
     * @param string $domain            
     * @return EnvironmentInterface
     */
    public function setDomain(string $domain): EnvironmentInterface;
}