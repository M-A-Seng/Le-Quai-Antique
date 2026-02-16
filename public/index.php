<?php

require __DIR__ . '/../app/Config/config.php';

use App\Core\Router;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router = new Router($routes);
$router->dispatch($method, $uri);