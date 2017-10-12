<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Interfaces;

/**
 * Interface for operation
 *
 * @author Christopher Kalkhoff
 *        
 */
interface OperationInterface
{

    /**
     * Execute operation
     *
     * @param mixed ...$arguments            
     * @return mixed
     */
    public function execute(ObjectOperationInterface $object, ...$arguments);

    /**
     * Get operation name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * If operation can be reverted
     *
     * @return bool
     */
    public function canRevert(): bool;

    /**
     * If operation can be queued
     *
     * @return bool
     */
    public function canQueue(): bool;
}