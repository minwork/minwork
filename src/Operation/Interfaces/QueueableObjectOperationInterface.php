<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Interfaces;

/**
 * Interface for object that supports queueable operations
 *
 * @author Christopher Kalkhoff
 *        
 */
interface QueueableObjectOperationInterface extends ObjectOperationInterface
{

    /**
     * Append operation to the queue
     *
     * @param OperationInterface $operation            
     * @param mixed ...$arguments            
     * @return self
     */
    public function addToQueue(OperationInterface $operation, ...$arguments): self;

    /**
     * Execute operations queue
     *
     * @param bool $clear
     *            If queue should be cleared afterwards
     */
    public function executeQueue(bool $clear = false);
}