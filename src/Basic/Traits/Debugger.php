<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Traits;

/**
 * Simple internal object debugger<br>
 * Stores debug messages with simplified backtrace inside an array for easy dump
 *
 * @author Christopher Kalkhoff
 *        
 */
trait Debugger 
{

    /**
     * Storage for debug messages
     *
     * @var string[]
     */
    protected $debug = [];

    /**
     * Save debug message with corresponding function call in internal array
     *
     * @param string $message            
     */
    protected function debug(string $message): self
    {
        $backtrace = debug_backtrace(0, 2);
        $selfData = $backtrace[0];
        $methodData = $backtrace[1];
        $args = $methodData['args'];
        
        $key = "{$methodData['function']}(" . implode(', ', array_map([
            '\Minwork\Helper\Formatter',
            'toString'
        ], $args)) . "):{$selfData['line']}";
        $this->debug[$key] = $message;
        
        return $this;
    }

    /**
     * Return debug array
     *
     * @return array
     */
    public function getDebug(): array
    {
        return $this->debug;
    }
}