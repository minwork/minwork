<?php
namespace Test;

require "vendor/autoload.php";

use Minwork\Storage\Object\AbstractArrayStorage;
use Minwork\Storage\Basic\Cookie;
use Minwork\Storage\Basic\Session;
use Minwork\Storage\Basic\Get;
use Minwork\Storage\Basic\Post;

class StorageTest extends \PHPUnit_Framework_TestCase {
    public function test()
    {
        $array = [];
        $key = 'test';
        $key2 = 'test2';
        $value = 'Test value';
        $value2 = new TestClass();
        $storages = [
            new class($array) extends AbstractArrayStorage {
                public function __construct($array) {
                    parent::__construct($array);
                }
            },
            new Cookie(),
            new Session(),
            new Get(),
            new Post(),
        ];
        foreach ($storages as $storage) {
            $this->assertNull($storage->get($key));
            $this->assertEquals(0, $storage->count($key));
            $this->assertFalse($storage->isset($key));
            $storage->set($key, $value);
            $this->assertTrue($storage->isset($key));
            $this->assertEquals(1, $storage->count($key));
            $this->assertEquals($value, $storage->get($key));
            $storage->set($key2, $value2);
            $this->assertTrue($storage->isset($key2));
            $this->assertEquals(1, $storage->count($key2));
            $this->assertEquals($value2, $storage->get($key2));
            $this->assertEquals('Test val', $storage->get($key2)->test);
            $storage->unset($key);
            $storage->unset($key2);
            $this->assertNull($storage->get($key));
            $this->assertNull($storage->get($key2));
            $this->assertFalse($storage->isset($key));
            $this->assertFalse($storage->isset($key2));
            $this->assertEquals(0, $storage->count($key));
            $this->assertEquals(0, $storage->count($key2));
        }
    }
}

class TestClass {
    public $test = 'Test val';
}