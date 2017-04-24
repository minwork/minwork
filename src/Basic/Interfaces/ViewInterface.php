<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Basic\Interfaces;

/**
 * Interface for objects containing page content for response
 *
 * @author Christopher Kalkhoff
 *        
 */
interface ViewInterface
{

    /**
     * Get view content in form of string
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get content type for headers
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Get object content
     *
     * @return mixed
     */
    public function getContent();
}