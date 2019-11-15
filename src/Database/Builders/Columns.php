<?php

namespace Minwork\Database\Builders;

use Minwork\Database\Traits\AbstractExpressionBuilder;
use Minwork\Helper\Arr;

class Columns extends AbstractExpressionBuilder
{
    /**
     * Columns constructor.
     * @param $columns
     */
    public function __construct($columns)
    {
        if (is_array($columns)) {
            // If columns have aliases
            if (Arr::isAssoc($columns)) {
                $this->addPartsList(function ($column) use ($columns) {
                    $this->addPart(self::TYPE_IDENTIFIER, $column)->addPart(self::TYPE_TOKEN, "as {$columns[$column]}");
                }, array_keys($columns));
            } else {
                $this->addPartsList(self::TYPE_IDENTIFIER, $columns);
            }
        } else {
            $this->addPart(self::TYPE_EXPRESSION, strval($columns));
        }
    }
}