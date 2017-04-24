<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Http\Interfaces;

/**
 * Interface for url object
 * 
 * @author Christopher Kalkhoff
 *        
 */
interface UrlInterface
{

    /**
     * Get url string representation
     * 
     * @return string
     */
    public function getUrl(): string;

    /**
     * Get url param
     * 
     * @param string $param            
     * @return string
     */
    public function getParam(string $param): string;

    /**
     * Append query param(s)
     * 
     * @param mixed $query            
     * @return self
     */
    public function appendQuery($query): self;
}