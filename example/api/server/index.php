<?php
require '../../../vendor/autoload.php';
require 'App/Config.php';

use Minwork\Core\Framework;
use Minwork\Http\Utility\Environment;
use Minwork\Http\Object\Router;

$framework = new Framework(new Router('App/Routing.php'), new Environment());
$framework->run($_GET['URL'] ?? '');