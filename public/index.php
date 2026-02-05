<?php

require __DIR__ . '/../vendor/autoload.php';
$routes = require __DIR__ . '/../config/routes.php';

use App\Core\Router;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router = new Router($routes);
$router->dispatch($method, $uri);