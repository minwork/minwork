<?php

namespace Test;

require "vendor/autoload.php";

use Minwork\Basic\Controller\Controller;
use Minwork\Basic\Utility\FlowEvent;
use Minwork\Core\Framework;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Event\Traits\Connector;
use Minwork\Http\Object\Response;
use Minwork\Http\Object\Router;
use Minwork\Http\Utility\Environment;
use Minwork\Http\Utility\HttpCode;
use PHPUnit_Framework_TestCase;

class FrameworkTest extends PHPUnit_Framework_TestCase
{

    public function testUrlParsing()
    {
        $url = '/test/test-method/lang-es/page-5/arg1/arg2:test2/arg3:test3,arg4:test4/arg5,arg6';

        $controller = new class() extends Controller
        {

            public function test_method()
            {
                return 'test';
            }
        };
        $router = new Router([
            'test' => $controller
        ]);
        $environment = new Environment();
        $framework = new Framework($router, $environment);

        $framework->run($url, true);

        $this->assertEquals('es', $framework->getRouter()
            ->getLang());
        $this->assertEquals(5, $framework->getRouter()
            ->getPage());
        $this->assertEquals('test_method', $framework->getRouter()
            ->getMethod());
        $this->assertEquals($controller, $framework->getRouter()
            ->getController());
        $this->assertEquals($url, $framework->getRouter()
            ->getUrl());
        $this->assertEquals([
            'arg1',
            'arg2' => 'test2',
            [
                'arg3' => 'test3',
                'arg4' => 'test4'
            ],
            [
                'arg5',
                'arg6'
            ]
        ], $framework->getRouter()
            ->getMethodArguments());
    }

    public function routingUrlProvider()
    {
        return [
            ['/prefix/test/test-method'], // Nested
            ['/prefix1/prefix2/test'], // Nested with default method
            ['http://username:password@hostname:9090/prefix1/prefix2/test/test-method?arg=value#anchor'], // Extract nested controller and method from full url
            ['/test-method'], // Default controller
            [''], // Default controller and method
            ['/test/test-method'], // Basic
            ['http://username:password@hostname:9090/?arg=value#anchor'], // Default controller and method from messy url (empty path)
            ['  in^va*l)(id<tag>  </tag> '], // Default controller and method from invalid url
        ];
    }

    /**
     * @dataProvider routingUrlProvider
     * @param $url
     */
    public function testRouting($url)
    {
        $counter = 0;
        $eventDispatcher = new EventDispatcher();
        $environment = new Environment();
        $breakFlowResponse = new Response('TestBreakFlowContent', Response::CONTENT_TYPE_JSON, HttpCode::BAD_REQUEST);

        $controller = new class($eventDispatcher, $counter, $breakFlowResponse) extends Controller
        {
            /* @var $this self */
            use Connector;

            public $break, $counter, $postProcess;

            private $breakFlowResponse;

            public function __construct($dispatcher, $counter, $breakFlowResponse)
            {
                parent::__construct();
                $this->counter = $counter;
                $this->break = false;
                $this->postProcess = false;

                $this->breakFlowResponse = $breakFlowResponse;
                $this->connect('\Minwork\Core\Framework', $dispatcher);
            }

            public function afterUrlTranslation()
            {
                $this->counter += 1;
            }

            public function beforeMethodRun(FlowEvent $event)
            {
                $this->counter += 2;
                if ($this->break) {
                    $event->breakFlow();
                    $this->setResponse($this->breakFlowResponse);
                }
            }

            public function beforeRun()
            {
                $this->counter += 3;
            }

            public function afterRun()
            {
                if ($this->postProcess) {
                    $this->getResponse()->setContent('PostProcessedContent');
                }
            }

            public function beforeOutput()
            {
                $this->counter += 5;
            }

            public function afterMethodRun()
            {
                $this->counter += 4;
                if ($this->postProcess) {
                    $this->getResponse()->setHttpCode(201);
                }
            }

            public function test_method()
            {
                return 'TestNormalFlowContent';
            }

            public function show()
            {
                return $this->test_method();
            }
        };

        $router = new Router([
            Router::DEFAULT_CONTROLLER_ROUTE_NAME => $controller,
            'test' => $controller,
            'prefix' => [
                'test' => $controller,
            ],
            'prefix1' => [
                'prefix2' => [
                    'test' => $controller,
                ],
            ],
        ]);

        $framework = new Framework($router, $environment, $eventDispatcher);

        $content = $framework->run($url, TRUE)->getContent();
        $this->assertSame(15, $controller->counter);
        $this->assertSame('TestNormalFlowContent', $content);

        $controller->break = true;
        $content = $framework->run($url, TRUE)->getContent();
        $this->assertSame('TestBreakFlowContent', $content);

        $controller->break = false;
        $controller->postProcess = true;
        $response = $framework->run($url, TRUE);
        $this->assertSame(201, $response->getHttpCode());
        $this->assertSame('PostProcessedContent', $response->getContent());
    }

    public function testResponse()
    {
        $response = new Response();
        $basicHeaders = $response->getHeaders();

        $response->setHeader('Test', 'abc');
        $this->assertSame(array_merge($basicHeaders, ['Test' => 'abc']), $response->getHeaders());

        $response->setHeader('test', 'def');
        $this->assertSame(array_merge($basicHeaders, ['test' => 'def']), $response->getHeaders());

        $response->setHeader('Foo: Bar');
        $this->assertSame(array_merge($basicHeaders, ['test' => 'def', 'Foo: Bar']), $response->getHeaders());

        $response->setHeader('TEST', 'xyz', false);
        $this->assertSame(array_merge($basicHeaders, ['test' => 'def', 'Foo: Bar', 'TEST' => 'xyz']), $response->getHeaders());

        $response->setHeader('TEst', 123, false);
        $this->assertSame(array_merge($basicHeaders, ['test' => 'def', 'Foo: Bar', 'TEST' => 'xyz', 'TEst' => 123]), $response->getHeaders());

        $response->removeHeader('TEst', false);
        $this->assertSame(array_merge($basicHeaders, ['test' => 'def', 'Foo: Bar', 'TEST' => 'xyz']), $response->getHeaders());

        $response->removeHeader('TEsT');
        $this->assertSame(array_merge($basicHeaders, ['Foo: Bar']), $response->getHeaders());

        $response->clearHeaders();
        $this->assertSame([], $response->getHeaders());
    }
}