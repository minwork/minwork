<?php

namespace Minwork\Database\Builders;

use Minwork\Database\Traits\AbstractExpressionBuilder;

class Limit extends AbstractExpressionBuilder
{

    /**
     * Limit constructor.
     * @param $limit
     */
    public function __construct($limit)
    {
        if (is_array($limit) && count($limit) == 2) {
            [$offset, $amount] = $limit;
            $this->addPart(self::TYPE_VALUE, intval($amount))->addPart(self::TYPE_TOKEN, 'OFFSET')->addPart(self::TYPE_VALUE, intval($offset));
        } elseif (is_int($limit)) {
            $this->addPart(self::TYPE_VALUE, $limit);
        } else {
            $this->addPart(self::TYPE_TOKEN, strval($limit));
        }
    }
}