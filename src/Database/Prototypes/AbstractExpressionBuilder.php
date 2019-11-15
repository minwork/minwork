<?php


namespace Minwork\Database\Traits;


use Minwork\Database\Interfaces\ExpressionBuilderInterface;
use Minwork\Helper\Arr;

abstract class AbstractExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * @var array
     */
    protected $_parts = [];

    /**
     * @var callable
     */
    protected $_idEscapeCb = null;

    /**
     * @var callable
     */
    protected $_valEscapeCb = null;

    protected $_values = [];

    protected $_mode = self::MODE_PLACEHOLDER;

    protected $_namedCounter = 1;

    protected function addPart(string $type, $value)
    {
        $this->_parts[] = [$type, $value];

        return $this;
    }

    /**
     * @param callable|string $type
     * @param $values
     * @return AbstractExpressionBuilder
     */
    protected function addPartsList($type, array $values)
    {
        $lastKey = Arr::getLastKey($values);
        foreach ($values as $key => $value)
        {
            if (is_string($type)) {
                $this->addPart($type, $value);
            } elseif (is_callable($type)) {
                $type($value, $this);
            }
            if ($key !== $lastKey) {
                $this->addPart(self::TYPE_TOKEN, ', ');
            }
        }

        return $this;
    }

    /**
     * @param int $mode
     * @return AbstractExpressionBuilder
     */
    public function setMode($mode): ExpressionBuilderInterface
    {
        $this->_mode = $mode;
        return $this;
    }

    public function getMode(): int
    {
        return $this->_mode;
    }

    public function setValueEscapeCallback(callable $callback): ExpressionBuilderInterface
    {
        $this->_valEscapeCb = $callback;

        return $this;
    }

    public function setIdentifierEscapeCallback(callable $callback): ExpressionBuilderInterface
    {
        $this->_idEscapeCb = $callback;

        return $this;
    }

    public function getValueEscapeCallback(): callable
    {
        // If no callback set then just return same value that was supplied
        if (is_null($this->_valEscapeCb)) {
            $this->setValueEscapeCallback(function ($var) { return $var; });
        }

        return $this->_valEscapeCb;
    }

    public function getIdentifierEscapeCallback(): callable
    {
        return $this->_idEscapeCb;
    }

    public function parse(): string
    {
        $stringArray = [];
        $this->_values = [];

        foreach ($this->_parts as $part) {
            [$type, $value] = $part;
            switch ($type) {
                case self::TYPE_IDENTIFIER:
                    $stringArray[] = call_user_func($this->getIdentifierEscapeCallback(), $value);
                    break;
                case self::TYPE_VALUE:
                    switch ($this->_mode) {
                        case self::MODE_PLACEHOLDER:
                            $this->_values[] = $value;
                            $value = '?';
                            break;
                        case self::MODE_NAMED_PLACEHOLDER:
                            $name = 'param' . $this->_namedCounter++;
                            $this->_values[$name] = $value;
                            $value = ":{$name}";
                            break;
                    }
                    $stringArray[] = call_user_func($this->getValueEscapeCallback(), $value);
                    break;
                case self::TYPE_BUILDER:
                    /** @var ExpressionBuilderInterface $value */
                    // Save current mode
                    $oldMode = $value->getMode();

                    $value
                        ->setIdentifierEscapeCallback($this->getIdentifierEscapeCallback())
                        ->setValueEscapeCallback($this->getValueEscapeCallback())
                        ->setMode($this->getMode());

                    $this->_values = array_merge($this->_values, $value->getValues());

                    $stringArray[] = "({$value})";

                    // Restore previous mode
                    $value->setMode($oldMode);
                    break;
                case self::TYPE_EXPRESSION:
                    $stringArray[] = "({$value})";
                    break;
                case self::TYPE_TOKEN:
                default:
                    $stringArray[] = "{$value}";
                    break;
            }
        }

        return implode(' ', $stringArray);
    }

    public function getValues(): array
    {
        return $this->_values;
    }

    public function __toString(): string
    {
        return $this->parse();
    }


}