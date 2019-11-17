<?php

namespace Test;

use Minwork\Validation\Object\Validator;
use PHPUnit_Framework_TestCase;

class ValidationTest extends PHPUnit_Framework_TestCase
{
    public function testIsValidFlag()
    {
        $validator = new Validator();

        $this->assertTrue($validator->isValid());
        $validator->setValid(false);
        $this->assertFalse($validator->isValid());
        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate();
        $this->assertTrue($validator->isValid());
        $validator->setValid(false);
        $this->assertFalse($validator->isValid());
        $validator->setValid(true);
        $this->assertTrue($validator->isValid());
    }
}