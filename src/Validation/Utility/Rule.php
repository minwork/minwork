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

    // Validation will stop immediately after this Rule conditions are not met
    const CRITICAL = 1;

    // When validation fails Error will not be added
    const SILENT = 2;

    /**
     * Function for checking data
     *
     * @var callable
     */
    protected $callback;

    /**
     * Value that callback should return in order to positively pass validation
     *
     * @var bool|mixed
     */
    protected $expect;

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
     * Various flags for different rule behaviours
     *
     * @var string
     */
    protected $flag;



    /**
     * Set rule config
     *
     * @param callable $callback
     *            Function to validate supplied data
     * @param ErrorInterface|null $error
     *            Default Error to display if validation fail. Null value will create generic message.
     * @param int|null $flag
     * @param mixed $expect What value callback should return to mark this rule as valid
     * @param mixed ...$arguments
     *            Additional arguments passed to callback (besides arguments passed to validate method)
     */
    public function __construct(callable $callback, ?ErrorInterface $error = null, ?int $flag = null, $expect = true, ...$arguments)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->expect = $expect;
        $this->error = $error ?? new Error('Rule check failed at ' . Formatter::toString($this->callback) . Formatter::toString($this->arguments));
        $this->flag = $flag ?? 0;
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
        
        $this->valid = ($this->callback)(...$arguments) === $this->expect;
        
        // If is invalid but has no errors, set default one
        if (! $this->valid && !($this->flag & self::SILENT) && ! $this->hasErrors()) {
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
        return $this->valid === false && $this->flag & self::CRITICAL;
    }
}