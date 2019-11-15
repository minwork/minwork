<?php

namespace Minwork\Database\Builders;

use Minwork\Database\Interfaces\ExpressionBuilderInterface;
use Minwork\Database\Traits\AbstractExpressionBuilder;
use Minwork\Helper\Validation;

class Group extends AbstractExpressionBuilder
{
    public function __construct($group)
    {
        if (is_array($group)) {
            $this->addPartsList(self::TYPE_IDENTIFIER, $group);
        } elseif (is_string($group) && Validation::isIdentifier($group)) {
            $this->addPart(self::TYPE_IDENTIFIER, $group);
        } elseif ($group instanceof ExpressionBuilderInterface) {
            $this->addPart(self::TYPE_BUILDER, $group);
        } else {
            $this->addPart(self::TYPE_EXPRESSION, $group);
        }
    }
}