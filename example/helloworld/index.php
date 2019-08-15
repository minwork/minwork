<?php
/*require '../../src/Core/Autoloader.php';
MinworkAutoloader::registerDefault();*/

require '../../vendor/autoload.php';

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
$framework = new Framework(new Router([Router::DEFAULT_CONTROLLER_ROUTE_NAME => $controller]), new Environment());
echo $framework->run('/World', true)->getContent();