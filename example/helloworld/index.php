<?php
require '../../src/Core/Autoloader.php';
MinworkAutoloader::registerDefault();

use Minwork\Core\Framework;
use Minwork\Http\Utility\Environment;
use Minwork\Http\Object\Router;
use Minwork\Basic\Controller\Controller;

$controller = new class() extends Controller {

    public function show($name)
    {
        return "Hello {$name}!";
    }
};
$framework = new Framework(new Router(['default_controller' => $controller]), new Environment());
echo $framework->run('/World', true)->getContent();