<?php

use App\Core\Response;
# helpers
require_once __DIR__ . '/../app/Helpers/main.php';
require_once __DIR__ . '/../app/Helpers/vite.php';
require_once __DIR__ . '/../app/Helpers/cloudinary.php';
# configuration / structure
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $response = $router->dispatch($method, $uri);
} 
catch (Throwable $e) {
    if (APPENV === 'dev') {
        echo $e->getMessage() . "\n" . $e->getTraceAsString();
    }
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    $content = $renderService->render('500', [], 'error');
    $response = new Response($content, 500, ['Content-Type' => 'text/html']);
}

$response->send();