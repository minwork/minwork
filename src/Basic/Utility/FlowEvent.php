<?php


namespace Minwork\Basic\Utility;


use Minwork\Event\Object\Event;

class FlowEvent extends Event
{
    protected $break = false;

    public function breakFlow(): void
    {
        $this->break = true;
    }

    public function shouldBreakFlow(): bool
    {
        return $this->break;
    }
}