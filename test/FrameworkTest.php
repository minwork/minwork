<?php
namespace Test;

require "vendor/autoload.php";

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
        
        $framework->run($url, TRUE);
        
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

    public function testRouting()
    {
        $url = '/test/test-method';
        $counter = 0;
        $break = false;
        $eventDispatcher = new EventDispatcher();
        $environment = new Environment();
        
        $controller = new class($eventDispatcher, $counter, $break) extends Controller {
            /* @var $this self */
            use Connector;

            public $counter;

            public $break;

            public function __construct($dispatcher, $counter, $break)
            {
                parent::__construct();
                $this->counter = $counter;
                $this->break = $break;
                $this->connect('\Minwork\Core\Framework', $dispatcher);
            }

            public function afterUrlTranslation()
            {
                $this->counter += 1;
            }

            public function beforeMethodRun()
            {
                $this->counter += 2;
                if ($this->break) {
                    $this->getResponse()
                        ->setContent('TestBreakFlowContent')
                        ->setContentType(Response::CONTENT_TYPE_JSON)
                        ->setHttpCode(HttpCode::BAD_REQUEST);
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

            public function beforeOutputContent()
            {
                $this->counter += 5;
            }

            public function test_method()
            {
                return 'TestNormalFlowContent';
            }
        };
        
        $router = new Router([
            'test' => $controller
        ]);
        
        $framework = new Framework($router, $environment, $eventDispatcher);
        
        $content = $framework->run($url, TRUE);
        $this->assertEquals(15, $controller->counter);
        $this->assertEquals('TestNormalFlowContent', $content);
        
        $controller->break = true;
        $content = $framework->run($url, TRUE);
        $this->assertEquals(new Response('TestBreakFlowContent', Response::CONTENT_TYPE_JSON, HttpCode::BAD_REQUEST), $content);
    }
}