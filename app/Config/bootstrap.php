<?php

namespace App\Config;

use App\Core\DIContainer;
use App\Core\Response;
use App\Services\RenderService;
use Throwable;

# Session
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

# Gestion des Exceptions non prévues
set_exception_handler(function (Throwable $e) 
{
    $logMessage = "[" . date('Y-m-d H:i:s') . "] "
                . $e->getMessage() . " in "
                . $e->getFile() . ":" . $e->getLine() . "\n"
                . $e->getTraceAsString() . "\n\n";

    $logFile = DIR_ROOT . '/logs/errors.log';
    error_log($logMessage, 3, $logFile);

    if ($_ENV['APP_ENV'] === 'dev') {
        echo "Une erreur interne est survenue. <br>";
        echo "<pre>" . $e . "</pre>";
    }
    else {
        $content = __DIR__ . '/../Views/errors/500.php';
        $response = new Response($content, 500, ['Content-Type' => 'text/html']);
        $response->send();
    }
});

# La seule instance de RenderService
$renderService = new RenderService();

# La seule instance du container d'injection des dépendances
$diContainer = new DIContainer($renderService);

# Router
$router = $diContainer->getRouter($routes, $diContainer); # $routes dans config.php