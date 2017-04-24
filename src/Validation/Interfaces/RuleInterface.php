<?php
namespace Minwork\Validation\Interfaces;

/**
 * Interface for valdation rule
 *
 * @author Christopher Kalkhoff
 *        
 */
interface RuleInterface
{

    const IMPORTANCE_CRITICAL = 'IMPORTANCE_CRITICAL';

    const IMPORTANCE_NORMAL = 'IMPORTANCE_NORMAL';

    /**
     * Check if conditions specified by the rule are met
     *
     * @return bool
     */
    public function check($value): bool;

    /**
     * Get text representation of error
     *
     * @return string
     */
    public function getError(): string;

    /**
     * Get rule name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get imporance of a rule<br>
     * If rule importance is critical in case of error during check validator should immediately finish validation returning false<br>
     * For normal importance all rules should be checked before returing final result of validation
     *
     * @return string
     */
    public function getImportance(): string;

    /**
     * Set validated object handle
     *
     * @param mixed $object            
     */
    public function setObject($object): self;

    /**
     * Get validated object
     */
    public function getObject();
}