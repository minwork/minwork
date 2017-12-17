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
    const IMPORTANCE_CRITICAL = 'critical';

    // Validation will continue after this rule conditions are not met
    const IMPORTANCE_NORMAL = 'normal';

    /**
     * Function for checking data
     *
     * @var callable
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
     * @param callable $callback
     *            Function to validate supplied data
     * @param string|null $error
     *            Error to diplay if validation fail. Empty string create generic message while null doesn't create any message
     * @param string $importance
     *            Importance of a rule
     * @param mixed ...$arguments
     *            Additional arguments passed to callback (besides arguments passed to validate method)
     */
    public function __construct(callable $callback, ?string $error = '', ?string $importance = null, ...$arguments)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->error = $error === '' ? 'Rule check failed at ' . Formatter::toString($this->callback) . Formatter::toString($this->arguments) : strval($error);
        $this->importance = $importance ?? self::IMPORTANCE_NORMAL;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\ValidatorInterface::validate()
     */
    public function validate(...$data): ValidatorInterface
    {
        $this->clearErrors();
        
        $arguments = $this->arguments;
        array_unshift($arguments, ...$data);
        
        $this->valid = boolval(($this->callback)(...$arguments));
        
        // If is invalid but has no errors, set default one
        if (! $this->valid && ! empty($this->error) && ! $this->hasErrors()) {
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