<?php
namespace Minwork\Database\Interfaces;

interface DatabaseInterface
{

    public function exec($statement);

    public function query($statement);

    /**
     * Cross-platform string escaping for preventing SQL injection<br>
     * <strong>Warning</strong>: This function add quotes to passed string
     *
     * @param string $string            
     */
    public function escape($value): string;

    public function getDriver(): string;

    public function getHost(): string;

    public function getName(): string;

    public function getCharset(): string;

    public function getOptions(): array;

    public function getLastInsertId();
}