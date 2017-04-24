<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Interfaces;

/**
 * Interface for object that supports operations
 * @author Christopher Kalkhoff
 *
 */
interface ObjectOperationInterface
{

    /**
     * Execute given operation
     * @param OperationInterface $operation
     * @param array $arguments
     */
    public function executeOperation(OperationInterface $operation, array $arguments);
}