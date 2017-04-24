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
 * @author Christopher Kalkhoff
 *
 */
interface OperationInterface {
    /**
     * Execute operation
     * @param array $arguments
     * @return mixed
     */
    public function execute(ObjectOperationInterface $object, array $arguments);
    /**
     * Get operation name
     * @return string
     */
    public function getName();
    /**
     * If operation can be reverted
     * @return bool
     */
    public function canRevert();
    /**
     * If operation can be queued
     * @return bool
     */
    public function canQueue();
    /**
     * Set operation name
     * @return self
     */
    public function setName($name);
    /**
     * Set if operation can be reverted
     * @return self
     */
    public function setCanRevert($bool);
    /**
     * Set if operation can be queued
     * @return self
     */
    public function setCanQueue($bool);
}