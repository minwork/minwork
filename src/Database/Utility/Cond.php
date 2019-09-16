<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Minwork\Database\Utility;

/**
 * Class Cond
 * Helper class to make creating conditions easier and more readable
 * @package Minwork\Database\Utility
 * @see \Minwork\Database\Utility\Condition
 */
class Cond
{
    /**
     * Shortcut for creating new condition with nested conditions joined by and() method
     * @param Condition ...$conditions
     * @return Condition
     */
    public static function andX(Condition ...$conditions): Condition
    {
        $self = new Condition();
        $count = count($conditions);
        foreach ($conditions as $index => $condition) {
            $self->condition($condition);
            if ($index < $count - 1) {
                $self->and();
            }
        }

        return $self;
    }

    /**
     * Shortcut for creating new condition with nested conditions joined by or() method
     * @param Condition ...$conditions
     * @return Condition
     */
    public static function orX(Condition ...$conditions): Condition
    {
        $self = new Condition();
        $count = count($conditions);
        foreach ($conditions as $index => $condition) {
            $self->condition($condition);
            if ($index < $count - 1) {
                $self->or();
            }
        }

        return $self;
    }

    /**
     * Create new condition and immediately add nested condition
     * @param Condition $condition
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::condition()
     */
    public static function nest(Condition $condition): Condition
    {
        return (new Condition())->condition($condition);
    }

    /**
     * Create new condition and add supplied expression to it
     * @param mixed $expression
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::expression()
     */
    public static function expr($expression): Condition
    {
        return (new Condition())->expression($expression);
    }

    /**
     * Shortcut for creating 'less than (or equal)' condition.<br>
     * <br>
     * <code>column < value</code><br>
     * <code>column <= value</code>
     *
     * @param string $column
     * @param $value
     * @param bool $equal
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::lt()
     * @see \Minwork\Database\Utility\Condition::lte()
     */
    public static function lt(string $column, $value, $equal = false): Condition
    {
        $self = (new Condition())->column($column);
        return $equal ? $self->lte($value) : $self->lt($value);
    }

    /**
     * Shortcut for creating 'greater than (or equal)' condition.<br>
     * <br>
     * <code>column > value</code><br>
     * <code>column >= value</code>
     *
     * @param string $column
     * @param $value
     * @param bool $equal
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::gt()
     * @see \Minwork\Database\Utility\Condition::gte()
     */
    public static function gt(string $column, $value, $equal = false): Condition
    {
        $self = (new Condition())->column($column);
        return $equal ? $self->gte($value) : $self->gt($value);
    }

    /**
     * Shortcut for creating '(not) equal' condition.<br>
     * <br>
     * <code>column = value</code><br>
     * <code>column <> value</code>
     *
     * @param string $column
     * @param $value
     * @param bool $not
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::equal()
     * @see \Minwork\Database\Utility\Condition::notEqual()
     */
    public static function eq(string $column, $value, $not = false): Condition
    {
        $self = (new Condition())->column($column);
        return $not ? $self->notEqual($value) : $self->equal($value);
    }

    /**
     * Shortcut for creating 'is (not) null' condition.<br>
     * <br>
     * <code>column IS NULL</code><br>
     * <code>column IS NOT NULL</code>
     *
     * @param string $column
     * @param bool $not
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::isNull()
     * @see \Minwork\Database\Utility\Condition::isNotNull()
     */
    public static function null(string $column, $not = false): Condition
    {
        $self = (new Condition())->column($column);
        return $not ? $self->isNotNull() : $self->isNull();
    }

    /**
     * Shortcut for creating '(not) like' condition.<br>
     * <br>
     * <code>column IS NULL</code><br>
     * <code>column IS NOT NULL</code>
     *
     * @param string $column
     * @param $value
     * @param string|null $wildcard
     * @param bool $not
     * @return Condition
     * @see \Minwork\Database\Utility\Condition::like()
     * @see \Minwork\Database\Utility\Condition::notLike()
     */
    public static function like(string $column, $value, ?string $wildcard = null, bool $not = false): Condition
    {
        $self = (new Condition())->column($column);
        return $not ? $self->notLike($value, $wildcard) : $self->like($value, $wildcard);
    }

    /**
     * Shortcut for creating 'between' condition.<br>
     * <br>
     * <code>column BETWEEN value1 AND value2</code><br>
     *
     * @param string $column
     * @param $value1
     * @param $value2
     * @return Condition
     */
    public static function between(string $column, $value1, $value2): Condition
    {
        return (new Condition())->column($column)->between($value1, $value2);
    }

    /**
     * Shortcut for creating 'in' condition.<br>
     * <br>
     * Also accepts $values as array -> in('column', ['val1', 'val2', ...])
     * <br>
     * <code>column IN (...values)</code><br>
     *
     * @param string $column
     * @param mixed ...$values
     * @return Condition
     */
    public static function in(string $column, ...$values): Condition
    {
        if (count($values) === 1 && is_array($values[0])) {
            $values = $values[0];
        }
        return (new Condition())->column($column)->in($values);
    }


    public static function createFromArray(array $conditions): Condition
    {
        $conditionsList = [];

        foreach ($conditions as $column => $value) {
            // If condition is string only, treat is as expression
            if (is_int($column) && is_string($value)) {
                $conditionsList[] = self::expr($value);
            } elseif (is_string($column)) { // If is proper column name
                // If string, numeric or bool use simple equality check
                if (is_string($value) || is_numeric($value) || is_bool($value)) {
                    $conditionsList[] = self::eq($column, $value);
                } elseif (is_array($value)) { // If array then use IN condition
                    $conditionsList[] = self::in($column, $value);
                } elseif (is_object($value)) { // If object then serialize it's value and do equality check
                    $conditionsList[] = self::eq($column, method_exists($value, '__toString') ? strval($value) : serialize($value));
                } elseif (is_null($value)) {
                    $conditionsList[] = self::null($column);
                }
            }
        }

        return self::andX(...$conditionsList);
    }
}