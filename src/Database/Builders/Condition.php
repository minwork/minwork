<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Minwork\Database\Utility;

use InvalidArgumentException;
use Minwork\Database\Interfaces\ExpressionBuilderInterface;
use Minwork\Database\Traits\AbstractExpressionBuilder;
use Minwork\Helper\Formatter;

/**
 * Helper class for storing complex query conditions and then converting them to string
 *
 * @author Christopher Kalkhoff
 *
 */
class Condition extends AbstractExpressionBuilder
{
    const WILDCARD_LEFT = 'left';
    const WILDCARD_RIGHT = 'right';
    const WILDCARD_BOTH = 'both';

    public function __construct($condition = null)
    {
        if (is_string($condition)) {
            $this->expression($condition);
        } elseif (is_array($condition)) {
            $this->condition(Cond::createFromArray($condition));
        } elseif ($condition instanceof self) {
            $this->condition($condition);
        }
    }

    /**
     * Add token element (any string that is a part of sql query)
     *
     * @param mixed $token
     *            Must be convertible to string
     * @return self
     */
    protected function addToken($token): ExpressionBuilderInterface
    {
        return $this->addPart(self::TYPE_TOKEN, $token);
    }

    /**
     * Add value element to query array
     *
     * @param mixed $value
     *            Must be valid argument for value escape function
     * @return self
     */
    protected function addVal($value): ExpressionBuilderInterface
    {
        return $this->addPart(self::TYPE_VALUE, $value);
    }

    /**
     * Append column to query.
     * This method should be called before appending condition specific syntax.
     *
     * @param string $name
     *            Unescaped column name
     * @return self
     */
    public function column(string $name): self
    {
        return $this->addPart(self::TYPE_IDENTIFIER, $name);
    }

    /**
     * Append condition to query to create complex conditions.
     * This object string value will be enclosed within brackets.
     *
     * @param self $condition
     * @return self
     */
    public function condition(self $condition): self
    {
        return $this->addPart(self::TYPE_BUILDER, $condition);
    }

    /**
     * Append expression (convertable to string) to query.
     *
     * @param mixed $expression
     * @return self
     */
    public function expression($expression): self
    {
        return $this->addToken($expression);
    }

    /**
     * Append AND syntax to qurey.
     * Must be preceded by column method.
     *
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function and(): self
    {
        return $this->addToken('AND');
    }

    /**
     * Append OR syntax to qurey.
     * Must be preceded by column method.
     *
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function or(): self
    {
        return $this->addToken('OR');
    }

    /**
     * Append BETWEEN 'value1' AND 'value2' syntax to qurey.
     * Must be preceded by column method.
     *
     * @param mixed $value1
     *            Must be valid escape value function argument
     * @param mixed $value2
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function between($value1, $value2): self
    {
        return $this->addToken('BETWEEN')
            ->addVal($value1)
            ->addToken('AND')
            ->addVal($value2);
    }

    /**
     * Append IN('value1', 'value2', ...) syntax to qurey.
     * Must be preceded by column method.
     *
     * @param array $array
     *            Array of unescaped values
     * @return self
     * @throws InvalidArgumentException
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function in(array $array): self
    {
        if (empty($array)) {
            throw new InvalidArgumentException('Array can not be empty');
        }
        $this->addToken('IN (');
        $arrayKeys = array_keys($array);
        $lastArrayKey = array_pop($arrayKeys);
        foreach ($array as $key => $value) {
            $this->addVal($value);
            if ($key !== $lastArrayKey) {
                $this->addToken(',');
            }
        }
        return $this->addToken(')');
    }

    /**
     * Append NOT IN('value1', 'value2', ...) syntax to qurey.
     * Must be preceded by column method.
     *
     * @param array $array
     *            Array of unescaped values
     * @return self
     * @throws InvalidArgumentException
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function notIn(array $array): self
    {
        if (empty($array)) {
            throw new InvalidArgumentException('Array can not be empty');
        }
        $this->addToken('NOT IN (');
        $arrayKeys = array_keys($array);
        $lastArrayKey = array_pop($arrayKeys);
        foreach ($array as $key => $value) {
            $this->addVal($value);
            if ($key !== $lastArrayKey) {
                $this->addToken(', ');
            }
        }
        return $this->addToken(')');
    }

    /**
     * Append IS NULL syntax to qurey.
     * Must be preceded by column method.
     *
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function isNull(): self
    {
        return $this->addToken('IS NULL');
    }

    /**
     * Append IS NOT NULL syntax to qurey.
     * Must be preceded by column method.
     *
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function isNotNull(): self
    {
        return $this->addToken('IS NOT NULL');
    }

    /**
     * Return new value according to specified wildcard type
     *
     * @param $value
     * @param string|null $wildcard
     * @return string
     */
    protected function parseWildcard($value, ?string $wildcard)
    {
        switch ($wildcard) {
            case self::WILDCARD_LEFT:
                $value = "%$value";
                break;
            case self::WILDCARD_RIGHT:
                $value = "$value%";
                break;
            case self::WILDCARD_BOTH:
                $value = "%$value%";
                break;
        }

        return $value;
    }

    /**
     * Append like value (column LIKE value) expression to query.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @param string|null $wildcard If to use wildcard (percent sign - '%') in like condition. Available options: 'left', 'right', 'both' or null (use class WILDCARD_* constants).
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function like($value, ?string $wildcard = null): self
    {
        return $this->addToken('LIKE')->addVal($this->parseWildcard($value, $wildcard));
    }

    /**
     * Append like value (column NOT LIKE value) expression to query.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @param string|null $wildcard If to use wildcard (percent sign - '%') in like condition. Available options: 'left', 'right', 'both' or null (use class WILDCARD_* constants).
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function notLike($value, ?string $wildcard = null): self
    {
        return $this->addToken('NOT LIKE')->addVal($this->parseWildcard($value, $wildcard));
    }

    /**
     * Append equal to value (column = value) expression to query.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function equal($value): self
    {
        return $this->addToken('=')->addVal($value);
    }

    /**
     * Append not equal to value (column <> value) expression to qurey.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function notEqual($value): self
    {
        return $this->addToken('<>')->addVal($value);
    }

    /**
     * Append greater than value (column > value) expression to qurey.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function gt($value): self
    {
        return $this->addToken('>')->addVal($value);
    }

    /**
     * Append greater than or equal to value (column >= value) expression to qurey.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function gte($value): self
    {
        return $this->addToken('>=')->addVal($value);
    }

    /**
     * Append less than value (column < value) expression to query.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function lt($value): self
    {
        return $this->addToken('<')->addVal($value);
    }

    /**
     * Append less than or equal to value (column <= value) expression to query.
     * Must be preceded by column method.
     *
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     * @see \Minwork\Database\Utility\Condition::column()
     */
    public function lte($value): self
    {
        return $this->addToken('<=')->addVal($value);
    }
}