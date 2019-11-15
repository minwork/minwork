<?php


namespace Minwork\Database\Interfaces;


interface ExpressionBuilderInterface
{
    const TYPE_IDENTIFIER = 'identifier';

    const TYPE_VALUE = 'value';

    const TYPE_EXPRESSION = 'expression';

    const TYPE_TOKEN = 'token';

    const TYPE_BUILDER = 'builder';

    const MODE_EXPLICIT = 1;
    const MODE_PLACEHOLDER = 2;
    const MODE_NAMED_PLACEHOLDER = 4;

    public function setValueEscapeCallback(callable $callback): self;

    public function setIdentifierEscapeCallback(callable $callback): self;

    public function getValueEscapeCallback(): callable;

    public function getIdentifierEscapeCallback(): callable;

    public function setMode($mode): ExpressionBuilderInterface;

    public function getMode();

    public function getValues(): array;

    public function parse(): string;

    public function __toString(): string;
}