<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Validation\Utility;

use Minwork\Validation\Interfaces\RuleInterface;

/**
 * Basic implementation of validation rule interface for validator
 *
 * @author Christopher Kalkhoff
 *        
 */
class Rule implements RuleInterface
{

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
     * Expected function result
     *
     * @var bool
     */
    protected $expect;

    /**
     * String repesentation of error
     *
     * @var string
     */
    protected $error;

    /**
     * Imporance of a rule which determines validator behaviour during rule check
     *
     * @see RuleInterface::getImportance()
     * @var string
     */
    protected $importance;

    /**
     * Validated object
     *
     * @var mixed
     */
    protected $object;

    /**
     * Set rule config
     *
     * @param string|callable $callback
     *            String if it's a method of Validation helper, callable otherwise
     * @param string $error
     *            Error to diplay if check fail
     * @param array $arguments
     *            Additional arguments passed to callback
     * @param bool $expect
     *            Expected callback return
     * @param string $importance
     *            Importance of a rule
     * @see RuleInterface::getImportance() for rule importance usage description
     */
    public function __construct($callback, string $error = '', array $arguments = [], $importance = self::IMPORTANCE_NORMAL, bool $expect = true)
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
        $this->error = $error;
        $this->importance = $importance;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::setObject()
     */
    public function setObject($object): RuleInterface
    {
        $this->object = $object;
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getObject()
     */
    public function getObject()
    {
        if (empty($this->object)) {
            throw new \Exception('No object is set');
        }
        return $this->object;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::check($value)
     */
    public function check($value): bool
    {
        $arguments = $this->arguments;
        array_unshift($arguments, $value);
        return call_user_func_array($this->callback, $arguments) === $this->expect;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getError()
     */
    public function getError(): string
    {
        // If error is empty produce default output
        if (empty($this->error)) {
            $this->error = "Rule check failed at method {$this->getName()}(" . implode(', ', $this->arguments) . ")";
        }
        return $this->error;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getName()
     */
    public function getName(): string
    {
        return (is_array($this->callback) ? implode('::', $this->callback) : $this->callback);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Validation\Interfaces\RuleInterface::getImportance()
     */
    public function getImportance(): string
    {
        return $this->importance;
    }
}