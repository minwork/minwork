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
 * Basic implementation of $_SESSION storage
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Session extends AbstractArrayStorage
{

    /**
     * If cannot access $_SESSION array manually initialize it
     * @throws \Exception
     */
    public function __construct(): void
    {
        if (! isset($_SESSION)) {
            if (PHP_SAPI === 'cli') {
                $_SESSION = array();
            } elseif (! headers_sent()) {
                if (! session_start()) {
                    throw new \Exception('Cannot initialize session storage - session_start failed');
                }
            } else {
                throw new \Exception('Cannot initialize session storage - headers was already sent or cannot access $_SESSION array');
            }
        }
        parent::__construct($_SESSION);
    }
}