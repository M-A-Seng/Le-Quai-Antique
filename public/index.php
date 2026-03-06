<?php

require __DIR__ . '/../app/Config/config.php';

use App\Core\DIContainer;
use App\Core\Router;

session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$diContainer = new DIContainer;
$router = new Router($routes, $diContainer);
$router->dispatch($method, $uri);