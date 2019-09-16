<?php
namespace Test;

use Minwork\Event\Object\Event;
use Minwork\Event\Object\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{

    public function testDispatcher()
    {
        $event = new Event('PropagationTestEvent');
        $dispatcher = new EventDispatcher();
        $listener1 = new class() {

            /* @var $this self */
            private $propagated = false;

            public function isPropagated()
            {
                return $this->propagated;
            }

            public function update(Event $e)
            {
                $this->propagated = true;
                if ($e->isActive()) {
                    $e->setActive(FALSE);
                }
            }
        };
        $listener2 = new class() {

            /* @var $this self */
            private $propagated = false;

            public function isPropagated()
            {
                return $this->propagated;
            }

            public function update()
            {
                $this->propagated = true;
            }
        };
        
        $dispatcher->addListener($event, [
            $listener1,
            "update"
        ]);
        $dispatcher->addListener($event, [
            $listener2,
            "update"
        ]);
        $dispatcher->dispatch($event);
        $this->assertTrue($listener1->isPropagated());
        $this->assertFalse($listener2->isPropagated());
        $this->assertFalse($event->isActive());
        
        $dispatcher->removeListener($event, [
            $listener1,
            "update"
        ]);
        $event->setActive(TRUE);
        $dispatcher->dispatch($event);
        $this->assertTrue($listener2->isPropagated());
    }

    public function testEvent()
    {
        $dataToTest = [
            new class() {

                public function test()
                {
                    return 'test';
                }
            },
            [
                'abc',
                1,
                1.7,
                NULL
            ],
            function ($abc) {
                return $abc . 'test';
            }
        ];
        $event = new Event('TestEvent', $dataToTest[0]);
        $dispatcher = new EventDispatcher();
        $listener1 = new class($dataToTest[1]) {

            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function update(Event $e)
            {
                $e->setData($this->data);
            }
        };
        $listener2 = new class($dataToTest[2]) {

            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function update(Event $e)
            {
                $e->setData($this->data);
            }
        };
        $this->assertEquals('TestEvent', $event->getName());
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals('test', $event->getData()[0]
            ->test());
        $dispatcher->addListener($event, [
            $listener1,
            "update"
        ]);
        $dispatcher->dispatch($event);
        $this->assertEquals([
            'abc',
            1,
            1.7,
            NULL
        ], $event->getData()[0]);
        $dispatcher->removeListener($event, [
            $listener1,
            "update"
        ]);
        $dispatcher->addListener($event, [
            $listener2,
            "update"
        ]);
        $dispatcher->dispatch($event);
        $this->assertEquals('testingtest', $event->getData()[0]('testing'));
    }
}