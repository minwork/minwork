<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Utility;

use Minwork\Error\Interfaces\ErrorInterface;
use Minwork\Error\Interfaces\ErrorsStorageContainerInterface;
use Minwork\Error\Object\Error;
use Minwork\Error\Traits\Errors;
use Minwork\Validation\Interfaces\ValidatorInterface;
use Minwork\Validation\Traits\Validator;
use Minwork\Helper\Formatter;

/**
 * Rule validator - defined by callback to validation function
 *
 * @author Christopher Kalkhoff
 *        
 */
class Rule implements ValidatorInterface, ErrorsStorageContainerInterface
{
    use Validator, Errors;

    // Validation will stop immediately after this rule conditions are not met
    const IMPORTANCE_CRITICAL = 'critical';

    // Validation will continue after this rule conditions are not met
    const IMPORTANCE_NORMAL = 'normal';

    // Error will not be added
    const IMPORTANCE_SILENT = 'silent';

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
     * @param ErrorInterface|null $error
     *            Default Error to display if validation fail. Null value will create generic message.
     * @param string $importance
     *            Importance of a rule (defaults to normal)
     * @param mixed ...$arguments
     *            Additional arguments passed to callback (besides arguments passed to validate method)
     */
    public function __construct(callable $callback, ?ErrorInterface $error = null, ?string $importance = null, ...$arguments)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->error = $error ?? new Error('Rule check failed at ' . Formatter::toString($this->callback) . Formatter::toString($this->arguments));
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
        if (! $this->valid && $this->importance !== self::IMPORTANCE_SILENT && ! $this->hasErrors()) {
            $this->getErrorsStorage()->addError($this->error);
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