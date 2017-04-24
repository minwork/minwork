<?php
namespace Minwork\Validation\Interfaces;

/**
 * Interface for valdation rule
 *
 * @author Christopher Kalkhoff
 *        
 */
interface FieldInterface
{

    /**
     * Return array of rules for field validation
     *
     * @return RuleInterface[]
     */
    public function getRules(): array;

    /**
     * If field is mandatory
     *
     * @see FieldInterface::getError() In case it is and there is no data specified for it field will return error
     * @return bool
     */
    public function isMandatory(): bool;

    /**
     * Get rule name
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Get text representation of error
     *
     * @return string
     */
    public function getError(): string;
}