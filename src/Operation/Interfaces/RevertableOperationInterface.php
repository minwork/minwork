<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Operation\Interfaces;

/**
 * Interface for operations that can be reverted
 * @author Christopher Kalkhoff
 *
 */
interface RevertableOperationInterface extends OperationInterface
{

    /**
     * Revert operation changes to object
     * @param RevertableObjectOperationInterface $object
     * @param array $arguments
     */
    public function revert(RevertableObjectOperationInterface $object, array $arguments);
}