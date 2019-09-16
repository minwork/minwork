<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Test;

use Minwork\Operation\Object\Operation;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Operation\Interfaces\RevertableObjectOperationInterface;
use Minwork\Operation\Traits\Operations;
use Minwork\Operation\Interfaces\RevertableOperationInterface;
use Minwork\Event\Traits\Connector;
use Minwork\Operation\Object\OperationEvent;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{

    public function test()
    {
        $counter = 0;
        $dispatcher = new EventDispatcher();
        
        $object = new ClassWithQueue($counter, $dispatcher);
        
        $operation1 = new class('operation_1', $dispatcher) extends Operation implements RevertableOperationInterface {

            public function __construct($name, $dispatcher)
            {
                parent::__construct($name, true, true, $dispatcher);
            }

            public function revert(RevertableObjectOperationInterface $object, ...$arguments)
            {
                return $this->execute($object, ...array_map(function ($arg) {
                    return ! $arg;
                }, $arguments));
            }
        };
        
        $operation2 = new class('operation_2', $dispatcher) extends Operation {

            public function __construct($name, $dispatcher)
            {
                parent::__construct($name, true, true, $dispatcher);
            }

            public function revert(RevertableObjectOperationInterface $object, ...$arguments)
            {
                return $this->execute($object, ...array_map(function ($arg) {
                    return ! $arg;
                }, $arguments));
            }
        };
        
        $object->addToQueue($operation1, false);
        $object->addToQueue($operation2, false);
        $object->executeQueue();
        $this->assertEquals($counter + 33, $object->counter);
        $object->revertQueue();
        $this->assertEquals($counter, $object->counter);
    }
}

class ClassWithQueue implements RevertableObjectOperationInterface
{
    use Operations, Connector;

    public $counter;

    public function __construct($counter, $dispatcher)
    {
        $this->counter = $counter;
        $this->connect([
            'beforeOperation_1',
            'afterOperation_2'
        ], $dispatcher);
    }

    public function beforeOperation_1(OperationEvent $event)
    {
        $revert = $event->getArguments()[0];
        $this->counter = $revert ? $this->counter - 10 : $this->counter + 10;
    }

    public function operation_1($revert)
    {
        $this->counter = $revert ? $this->counter - 1 : $this->counter + 1;
    }

    public function operation_2($revert)
    {
        $this->counter = $revert ? $this->counter - 2 : $this->counter + 2;
    }

    public function afterOperation_2(OperationEvent $event)
    {
        $revert = $event->getArguments()[0];
        $this->counter = $revert ? $this->counter - 20 : $this->counter + 20;
    }
}