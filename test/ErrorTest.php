<?php
namespace Test;

require "vendor/autoload.php";

use Minwork\Error\Object\Errors;
use Minwork\Error\Basic\ErrorForm;
use Minwork\Error\Basic\ErrorGlobal;

class ErrorTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $storage = new Errors();
        $storage2 = new Errors();
        
        $errorGlobal1 = new ErrorGlobal('Test 1');
        $errorGlobal2 = new ErrorGlobal('Test 2');
        $errorGlobal3 = new ErrorGlobal('Test 3');
        $errorForm1 = new ErrorForm('test1', 'Test msg 1');
        $errorForm2 = new ErrorForm('test2', 'Test msg 2');
        $errorForm3 = new ErrorForm('test3', 'Test msg 3');
        
        $this->assertEquals(ErrorGlobal::TYPE, $errorGlobal1->getType());
        $this->assertEquals('Test 1', $errorGlobal1->getMessage());
        
        $this->assertEquals(ErrorForm::TYPE, $errorForm1->getType());
        $this->assertEquals('Test msg 1', $errorForm1->getMessage());
        $this->assertTrue($errorForm1->hasFieldName());
        $this->assertEquals('test1', $errorForm1->getFieldName());
        
        $this->assertFalse($storage->hasErrors());
        $this->assertEmpty($storage->getErrors());
        
        $storage->addError($errorGlobal1);
        $storage->addError($errorGlobal2);
        $storage->addError($errorForm1);
        $storage->addError($errorForm2);
        
        $this->assertTrue($storage->hasErrors());
        $this->assertEquals([
            'test1' => $errorForm1,
            'test2' => $errorForm2
        ], $storage->getErrors(ErrorForm::TYPE));
        $this->assertEquals([
            $errorGlobal1,
            $errorGlobal2
        ], $storage->getErrors(ErrorGlobal::TYPE));
        $this->assertEquals([
            ErrorGlobal::TYPE => [
                $errorGlobal1,
                $errorGlobal2
            ],
            ErrorForm::TYPE => [
                'test1' => $errorForm1,
                'test2' => $errorForm2
            ]
        ], $storage->getErrors());
        
        $storage2->addError($errorGlobal3);
        $storage2->addError($errorForm3);
        
        $storage->merge($storage2);
        $this->assertEquals([
            ErrorGlobal::TYPE => [
                $errorGlobal1,
                $errorGlobal2,
                $errorGlobal3
            ],
            ErrorForm::TYPE => [
                'test1' => $errorForm1,
                'test2' => $errorForm2,
                'test3' => $errorForm3
            ]
        ], $storage->getErrors());
        $storage->clearErrors();
        $this->assertEmpty($storage->getErrors());
        $this->assertFalse($storage->hasErrors());
    }
}