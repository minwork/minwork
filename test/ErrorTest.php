<?php

namespace Test;

require "vendor/autoload.php";

use Minwork\Error\Object\Errors;
use Minwork\Error\Basic\FieldError;
use Minwork\Error\Object\Error;

class ErrorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $storage = new Errors();
        $storage2 = new Errors();

        $errorGlobal1 = new Error('Test 1');
        $errorGlobal2 = new Error('Test 2');
        $errorGlobal3 = new Error('Test 3');
        $fieldError1 = new FieldError('test1', 'Test msg 1');
        $fieldError2 = new FieldError('test2', 'Test msg 2');
        $fieldError3 = new FieldError('test3', 'Test msg 3');

        $this->assertEquals(Error::TYPE, $errorGlobal1->getType());
        $this->assertEquals('Test 1', $errorGlobal1->getMessage());

        $this->assertEquals(FieldError::TYPE, $fieldError1->getType());
        $this->assertEquals('Test msg 1', $fieldError1->getMessage());
        $this->assertTrue($fieldError1->hasRef());
        $this->assertEquals('test1', $fieldError1->getRef());

        $this->assertFalse($storage->hasErrors());
        $this->assertEmpty($storage->getErrors());

        $storage->addError($errorGlobal1);
        $storage->addError($errorGlobal2);
        $storage->addError($fieldError1);
        $storage->addError($fieldError2);

        $this->assertTrue($storage->hasErrors());
        $this->assertEquals([
            2 => $fieldError1,
            3 => $fieldError2
        ], $storage->getErrors(FieldError::TYPE));
        $this->assertEquals([
            $errorGlobal1,
            $errorGlobal2
        ], $storage->getErrors(Error::TYPE));
        $this->assertEquals([
            $errorGlobal1,
            $errorGlobal2,
            $fieldError1,
            $fieldError2,
        ], $storage->getErrors());

        $storage2->addError($errorGlobal3);
        $storage2->addError($fieldError3);

        $storage->merge($storage2);
        $this->assertEquals([
            $errorGlobal1,
            $errorGlobal2,
            $fieldError1,
            $fieldError2,
            $errorGlobal3,
            $fieldError3,
        ], $storage->getErrors());
        $storage->clearErrors();
        $this->assertEmpty($storage->getErrors());
        $this->assertFalse($storage->hasErrors());
    }
}