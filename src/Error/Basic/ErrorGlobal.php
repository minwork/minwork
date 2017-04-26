<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Error\Basic;

use Minwork\Error\Object\ErrorPrototype;

/**
 * Global error - message only
 *
 * @author Christopher Kalkhoff
 *        
 */
class ErrorGlobal extends ErrorPrototype
{

    const TYPE = "global_error";

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Error\Object\ErrorPrototype::getType()
     */
    public function getType(): string
    {
        return self::TYPE;
    }
}