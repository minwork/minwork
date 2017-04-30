<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Utility;

use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator;
use Minwork\Helper\Formatter;

/**
 * Rule validator - defined by callback to validation function
 *
 * @author Christopher Kalkhoff
 *        
 */
class Rule implements ValidatorInterface
{
    use Validator;
    
    // Validation will break immidietely after this rule conditions are not met
    const IMPORTANCE_CRITICAL = 'IMPORTANCE_CRITICAL';
    // Validation will continue after this rule conditions are not met
    const IMPORTANCE_NORMAL = 'IMPORTANCE_NORMAL';

    /**
     * Function for checking data
     *
     * @var callback
     */
    protected $callback;

    /**
     * Callback function arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Error to add when rule validation fails
     *
     * @var string
     */
    protected $error;

    /**
     * Expected function result
     *
     * @var bool
     */
    protected $expect;

    /**
     * Imporance of a rule which determines validator behaviour during rule check
     * If rule importance is critical in case of error during check validator should immediately finish validation returning false<br>
     * For normal importance all rules should be checked before returing final result of validation
     *
     * @var string
     */
    protected $importance;

    /**
     * Set rule config
     *
     * @param string|callable $callback
     *            String if it's a method of Validation helper, callable otherwise
     * @param string $error
     *            Error to diplay if validation fail
     * @param array $arguments
     *            Additional arguments passed to callback
     * @param bool $expect
     *            Expected callback return
     * @param string $importance
     *            Importance of a rule
     * @param bool $expect
     *            Expected function result
     */
    public function __construct($callback, string $error = '', array $arguments = [], string $importance = self::IMPORTANCE_NORMAL, bool $expect = true)
    {
        if (is_string($callback) && ! is_callable($callback) && method_exists("\Minwork\Helper\Validation", $callback)) {
            $callback = "\Minwork\Helper\Validation::{$callback}";
        }
        if (! is_callable($callback)) {
            throw new \InvalidArgumentException("Callback is neither callable nor Validation method ({$callback})");
        }
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->expect = $expect;
        $this->error = empty($error) ? 'Rule check failed at method ' . (is_array($callback) ? implode('::', $callback) : strval($callback)) . '(' . Formatter::toString($arguments) . ')' : $error;
        $this->importance = $importance;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::validate()
     */
    public function validate($data): ValidatorInterface
    {
        $this->clearErrors();
        
        $arguments = $this->arguments;
        array_unshift($arguments, $data);
        $this->valid = call_user_func_array($this->callback, $arguments) === $this->expect;
        if (! $this->valid) {
            $this->addError($this->error);
        }
        return $this;
    }

    /**
     * If whole validation should stop when this rule conditions are not met
     *
     * @return bool
     */
    public function hasCriticalError(): bool
    {
        return $this->valid === false && $this->importance === self::IMPORTANCE_CRITICAL;
    }
}