<?php
namespace Minwork\Basic\Traits;

trait Debugger 
{
    protected $debug = [];
    
    /**
     * Save debug message with corresponding function call in internal array
     * @param string $message
     */
    protected function debug(string $message)
    {
        $backtrace = debug_backtrace(0, 2);
        $selfData = $backtrace[0];
        $methodData = $backtrace[1];
        $args = $methodData['args'];
        
        $key = "{$methodData['function']}(".implode(', ', array_map(['\Minwork\Helper\Formatter', 'toString'], $args))."):{$selfData['line']}";
        $this->debug[$key] = $message;
    }
    
    /**
     * Return debug array
     * @return array
     */
    public function getDebug(): array
    {
        return $this->debug;
    }
}