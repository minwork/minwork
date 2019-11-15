<?php

namespace Minwork\Database\Builders;

use Minwork\Basic\Traits\Debugger;
use Minwork\Database\Traits\AbstractExpressionBuilder;
use Minwork\Helper\Validation;

class Order extends AbstractExpressionBuilder
{
    use Debugger;

    public function __construct($order)
    {
        if (is_array($order)) {
            foreach ($order as $key => $value) {
                if (is_string($key)) {
                    if (is_string($value)) {
                        if (Validation::isIdentifier($key)) {
                            $this->addPart(self::TYPE_IDENTIFIER, $key)->addPart(self::TYPE_VALUE, $value);
                        } else {
                            $this->addPart(self::TYPE_TOKEN, "{$key} {$value}");
                        }
                    } elseif (is_int($value) || is_bool($value)) {
                        $this->addPart(self::TYPE_IDENTIFIER, $key)->addPart(self::TYPE_TOKEN, $value > 0 ? 'ASC' : 'DESC');
                    } else {
                        $this->debug("Order value part should be either string, integer or boolean - using default sorting for {$key}");
                        $this->addPart(self::TYPE_IDENTIFIER, $key);
                    }
                } elseif (is_int($key) && is_string($value)) {
                    if (Validation::isIdentifier($value)) {
                        $this->addPart(self::TYPE_IDENTIFIER, $value);
                    } else {
                        $this->addPart(self::TYPE_TOKEN, $value);
                    }
                }
            }
        } elseif (is_string($order)) {
            if (Validation::isIdentifier($order)) {
                $this->addPart(self::TYPE_IDENTIFIER, $order);
            } else {
                $this->addPart(self::TYPE_TOKEN, $order);
            }
        }

        $this->addPart(self::TYPE_TOKEN, strval($order));
    }
}