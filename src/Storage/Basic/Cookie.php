<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Storage\Basic;

use Minwork\Storage\Object\AbstractArrayStorage;

/**
 * Basic implementation of $_COOKIE storage
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Cookie extends AbstractArrayStorage
{

    public function __construct(): void
    {
        parent::__construct($_COOKIE);
    }
}