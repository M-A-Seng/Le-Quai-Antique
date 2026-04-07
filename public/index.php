<?php

use App\Core\Response;

define('DIR_ROOT', dirname(__DIR__));

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $response = $router->dispatch($method, $uri);
} 
catch (Throwable $e) {
    $content = $renderService->render('500', [], 'error');
    $response = new Response($content, 500, ['Content-Type' => 'text/html']);
}

$response->send();