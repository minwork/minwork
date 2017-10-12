<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Utility;

use Minwork\Helper\Formatter;

/**
 * Helper class for storing complex query conditions and then converting them to string
 *
 * @author Christopher Kalkhoff
 *        
 */
class Condition
{

    const TYPE_COLUMN = 'column';

    const TYPE_VALUE = 'value';

    const TYPE_EXPRESSION = 'expression';

    const TYPE_CONDITION = 'condition';

    /**
     * Array containing parts of query in form of list of 2 elements lists (type and value)
     *
     * @var array
     */
    protected $query;

    /**
     * Function used to escape query elements with value type
     *
     * @var callable
     */
    protected $valueEscapeFunction;

    /**
     * Function used to escape query elements with column type
     *
     * @var callable
     */
    protected $columnEscapeFunction;

    /**
     *
     * @param callable $valueEscapeFunction            
     * @param callable $columnEscapeFunction            
     */
    public function __construct(callable $valueEscapeFunction = null, callable $columnEscapeFunction = null)
    {
        $valueEscapeFunction = is_null($valueEscapeFunction) ? function ($value) {
            return "'" . Formatter::cleanData(Formatter::removeQuotes($value)) . "'";
        } : $valueEscapeFunction;
        $columnEscapeFunction = is_null($columnEscapeFunction) ? function ($column) {
            return Formatter::cleanData(Formatter::removeQuotes($column));
        } : $columnEscapeFunction;
        $this->setValueEscapeFunction($valueEscapeFunction)->setColumnEscapeFunction($columnEscapeFunction);
    }

    /**
     * Add column element to query array
     *
     * @param string $name
     *            Unescaped column name
     * @return self
     */
    protected function addColumn(string $name): self
    {
        $this->query[] = [
            self::TYPE_COLUMN,
            $name
        ];
        return $this;
    }

    /**
     * Add condition object to query array
     *
     * @param self $condition            
     * @return self
     */
    protected function addCondition(self $condition): self
    {
        $this->query[] = [
            self::TYPE_CONDITION,
            $condition
        ];
        return $this;
    }

    /**
     * Add expression element to query array
     *
     * @param mixed $expression
     *            Must be convertable to string
     * @return self
     */
    protected function addExpression($expression): self
    {
        $this->query[] = [
            self::TYPE_EXPRESSION,
            $expression
        ];
        return $this;
    }

    /**
     * Add value element to query array
     *
     * @param mixed $value
     *            Must be valid argument for value escape function
     * @return self
     */
    protected function addValue($value): self
    {
        $this->query[] = [
            self::TYPE_VALUE,
            $value
        ];
        return $this;
    }

    /**
     * Convert condition to string applying escape functions and converting query array elements to string
     *
     * @return string
     */
    public function __toString(): string
    {
        $stringArray = [];
        foreach ($this->query as $queryPart) {
            list ($type, $var) = $queryPart;
            switch ($type) {
                case self::TYPE_COLUMN:
                    $stringArray[] = call_user_func($this->columnEscapeFunction, $var);
                    break;
                case self::TYPE_VALUE:
                    $stringArray[] = call_user_func($this->valueEscapeFunction, $var);
                    break;
                case self::TYPE_CONDITION:
                    $stringArray[] = "({$var->setColumnEscapeFunction($this->columnEscapeFunction)->setValueEscapeFunction($this->valueEscapeFunction)})";
                    break;
                case self::TYPE_EXPRESSION:
                default:
                    $stringArray[] = strval($var);
                    break;
            }
        }
        return implode(' ', $stringArray);
    }

    /**
     * Set value escape function
     *
     * @param callable $function            
     * @return self
     */
    public function setValueEscapeFunction(callable $function): self
    {
        $this->valueEscapeFunction = $function;
        return $this;
    }

    /**
     * Set column escape function
     *
     * @param callable $function            
     * @return self
     */
    public function setColumnEscapeFunction(callable $function): self
    {
        $this->columnEscapeFunction = $function;
        return $this;
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
        return $this->addColumn($name);
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
        return $this->addCondition($condition);
    }

    /**
     * Append expression (convertable to string) to query.
     *
     * @param mixed $expression            
     * @return self
     */
    public function expression($expression): self
    {
        return $this->addExpression($expression);
    }

    /**
     * Append AND syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @return self
     */
    public function and(): self
    {
        return $this->addExpression('AND');
    }

    /**
     * Append OR syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @return self
     */
    public function or(): self
    {
        return $this->addExpression('OR');
    }

    /**
     * Append BETWEEN 'value1' AND 'value2' syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value1
     *            Must be valid escape value function argument
     * @param mixed $value2
     *            Must be valid escape value function argument
     * @return self
     */
    public function between($value1, $value2): self
    {
        return $this->addExpression('BETWEEN')
            ->addValue($value1)
            ->addExpression('AND')
            ->addValue($value2);
    }

    /**
     * Append IN('value1', 'value2', ...) syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param array $array
     *            Array of unescaped values
     * @throws \InvalidArgumentException
     * @return self
     */
    public function in(array $array): self
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Array can not be empty');
        }
        $this->addExpression('IN (');
        $arrayKeys = array_keys($array);
        $lastArrayKey = array_pop($arrayKeys);
        foreach ($array as $key => $value) {
            $this->addValue($value);
            if ($key !== $lastArrayKey) {
                $this->addExpression(',');
            }
        }
        return $this->addExpression(')');
    }

    /**
     * Append NOT IN('value1', 'value2', ...) syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param array $array
     *            Array of unescaped values
     * @throws \InvalidArgumentException
     * @return self
     */
    public function notIn(array $array): self
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Array can not be empty');
        }
        $this->addExpression('NOT IN (');
        $arrayKeys = array_keys($array);
        $lastArrayKey = array_pop($arrayKeys);
        foreach ($array as $key => $value) {
            $this->addValue($value);
            if ($key !== $lastArrayKey) {
                $this->addExpression(', ');
            }
        }
        return $this->addExpression(')');
    }

    /**
     * Append IS NULL syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @return self
     */
    public function isNull(): self
    {
        return $this->addExpression('IS NULL');
    }

    /**
     * Append IS NOT NULL syntax to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @return self
     */
    public function isNotNull(): self
    {
        return $this->addExpression('IS NOT NULL');
    }

    /**
     * Append equal to value (column = value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function equal($value): self
    {
        return $this->addExpression('=')->addValue($value);
    }

    /**
     * Append not equal to value (column <> value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function notEqual($value): self
    {
        return $this->addExpression('<>')->addValue($value);
    }

    /**
     * Append greater than value (column > value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function gt($value): self
    {
        return $this->addExpression('>')->addValue($value);
    }

    /**
     * Append greater than or equal to value (column >= value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function gte($value): self
    {
        return $this->addExpression('>=')->addValue($value);
    }

    /**
     * Append lower than value (column < value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function lt($value): self
    {
        return $this->addExpression('<')->addValue($value);
    }

    /**
     * Append lower than or equal to value (column <= value) expression to qurey.
     * Must be preceded by column method.
     *
     * @see \Minwork\Database\Utility\Condition::column()
     * @param mixed $value
     *            Must be valid escape value function argument
     * @return self
     */
    public function lte($value): self
    {
        return $this->addExpression('<=')->addValue($value);
    }
}