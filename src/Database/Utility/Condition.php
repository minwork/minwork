<?php
namespace Minwork\Database\Utility;

use Minwork\Helper\Formatter;

class Condition
{

    const TYPE_COLUMN = 'column';

    const TYPE_VALUE = 'value';

    const TYPE_EXPRESSION = 'expression';
    
    const TYPE_CONDITION = 'condition';

    /**
     *
     * @var array
     */
    protected $query;

    protected $valueEscapeFunction;

    protected $columnEscapeFunction;

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

    protected function addColumn(string $name): self
    {
        $this->query[] = [
            self::TYPE_COLUMN,
            $name
        ];
        return $this;
    }
    
    protected function addCondition(self $condition): self
    {
        $this->query[] = [
            self::TYPE_CONDITION,
            $condition
        ];
        return $this;
    }

    protected function addExpression($expression): self
    {
        $this->query[] = [
            self::TYPE_EXPRESSION,
            $expression
        ];
        return $this;
    }

    protected function addValue($value): self
    {
        $this->query[] = [
            self::TYPE_VALUE,
            $value
        ];
        return $this;
    }

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

    public function setValueEscapeFunction(callable $function): self
    {
        $this->valueEscapeFunction = $function;
        return $this;
    }

    public function setColumnEscapeFunction(callable $function): self
    {
        $this->columnEscapeFunction = $function;
        return $this;
    }

    public function column(string $name): self
    {
        return $this->addColumn($name);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::condition()
     */
    public function condition(self $condition): self
    {
        return $this->addCondition($condition);
    }

    public function expression($expression): self
    {
        return $this->addExpression($expression);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::and()
     */
    public function and(): self
    {
        return $this->addExpression('AND');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::or()
     */
    public function or(): self
    {
        return $this->addExpression('OR');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::between()
     */
    public function between($value1, $value2): self
    {
        return $this->addExpression('BETWEEN')
            ->addValue($value1)
            ->addExpression('AND')
            ->addValue($value2);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::in()
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
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::notIn()
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
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::isNull()
     */
    public function isNull(): self
    {
        return $this->addExpression('IS NULL');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::isNotNull()
     */
    public function isNotNull(): self
    {
        return $this->addExpression('IS NOT NULL');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::equal()
     */
    public function equal($value): self
    {
        return $this->addExpression('=')->addValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::notEqual()
     */
    public function notEqual($value): self
    {
        return $this->addExpression('<>')->addValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::gt()
     */
    public function gt($value): self
    {
        return $this->addExpression('>')->addValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::gte()
     */
    public function gte($value): self
    {
        return $this->addExpression('>=')->addValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::lt()
     */
    public function lt($value): self
    {
        return $this->addExpression('<')->addValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Minwork\Database\Interfaces\self::lte()
     */
    public function lte($value): self
    {
        return $this->addExpression('<=')->addValue($value);
    }
}