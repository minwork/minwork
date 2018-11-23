<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Storage\Basic;

use Minwork\Storage\Object\AbstractArrayStorage;
use Minwork\Storage\Interfaces\StorageInterface;
use Minwork\Helper\Formatter;

/**
 * Basic implementation of $_COOKIE storage
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Cookie extends AbstractArrayStorage
{

    public function __construct()
    {
        parent::__construct($_COOKIE);
    }
    
    public function set($key, $value): StorageInterface
    {
        $args = array_slice(func_get_args(), 2);
        setcookie(Formatter::toString($key, false), $value, ...$args);
        
        return $this;
    }
    
    public function unset($key): StorageInterface
    {
        setcookie(Formatter::toString($key, false), '', time() - 3600);
        return $this;
    }
}