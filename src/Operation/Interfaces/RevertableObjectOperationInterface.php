<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Interfaces;

/**
 * Interface for object that supports revertable operations
 *
 * @author Christopher Kalkhoff
 *        
 */
interface RevertableObjectOperationInterface extends QueueableObjectOperationInterface
{

    /**
     * Prepend operation to revert queue
     *
     * @param OperationInterface $operation            
     * @param mixed ...$arguments            
     * @return self
     */
    public function addToRevertQueue(OperationInterface $operation, ...$arguments): self;

    /**
     * Execute revert operations queue
     *
     * @param bool $clear
     *            If queue should be cleared afterwards
     */
    public function revertQueue(bool $clear = false);
}