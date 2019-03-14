<?php
namespace Test;

require "vendor/autoload.php";

use Minwork\Basic\Utility\FlowEvent;
use Minwork\Core\Framework;
use Minwork\Basic\Controller\Controller;
use Minwork\Http\Object\Response;
use Minwork\Http\Utility\HttpCode;
use Minwork\Http\Object\Router;
use Minwork\Event\Traits\Connector;
use Minwork\Event\Object\EventDispatcher;
use Minwork\Http\Utility\Environment;
use Minwork\Http\Utility\Server;

class FrameworkTest extends \PHPUnit_Framework_TestCase
{

    public function testUrlParsing()
    {
        $url = '/test/test-method/lang-es/page-5/arg1/arg2:test2/arg3:test3,arg4:test4/arg5,arg6';
        
        Server::getDocumentRoot();
        
        $controller = new class() extends Controller {

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
            ['/prefix1/prefix2/test/test-method'], // Nested
            ['/test-method'], // Default
            ['/test/test-method'], // Basic
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

        $controller = new class($eventDispatcher, $counter, $breakFlowResponse) extends Controller {
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
                $this->counter += 4;
            }

            public function beforeOutput()
            {
                $this->counter += 5;
            }

            public function afterMethodRun()
            {
                if ($this->postProcess) {
                    $this->getResponse()->setContent('PostProcessedContent');
                }
            }

            public function test_method()
            {
                return 'TestNormalFlowContent';
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

        $content = $framework->run($url, TRUE);
        $this->assertEquals(15, $controller->counter);
        $this->assertEquals('TestNormalFlowContent', $content);

        $controller->break = true;
        $content = $framework->run($url, TRUE);
        $this->assertEquals('TestBreakFlowContent', $content);

        $controller->break = false;
        $controller->postProcess = true;
        $content = $framework->run($url, TRUE);
        $this->assertEquals('PostProcessedContent', $content);
    }
}