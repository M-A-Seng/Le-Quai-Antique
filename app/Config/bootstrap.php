<?php

use App\Core\DIContainer;
use App\Core\Response;
use App\Exceptions\ServerException;
use App\Services\RenderService;
use Dotenv\Dotenv;
use function App\Helpers\get_valid_env;

# Chargement autmatique des dépendances dans les fichers php
require_once DIR_ROOT . '/vendor/autoload.php';

if (file_exists(DIR_ROOT . '/.env')) {
    # chargement des variables d'environnement
    $dotenv = Dotenv::createImmutable(DIR_ROOT);
    if (get_valid_env('APP_ENV') === 'prod') {
        $dotenv->safeLoad();
    } else {
        $dotenv->load();
    }
}

# Variables globales
define('APPENV', get_valid_env('APP_ENV'));
define('APP_PROTECTED', get_valid_env('APP_PROTECTED'));

// ne pas indéxer (moteurs de recherche) si dev ou protégé
if (APPENV === 'dev' || APP_PROTECTED === 'true') {
    header("X-Robots-Tag: noindex, nofollow, noarchive, nosnippet");
}

# Session
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

# Gestion des Exceptions non prévues
set_exception_handler(function (\Throwable $e) 
{
    $logMessage = "[" . date('Y-m-d H:i:s') . "] "
                . $e->getMessage() . " in "
                . $e->getFile() . ":" . $e->getLine() . "\n"
                . $e->getTraceAsString() . "\n\n";

    $logFile = DIR_ROOT . '/logs/errors.log';
    error_log($logMessage, 3, $logFile);

    if (APPENV === 'dev') {
        echo "Une erreur interne est survenue. <br>";
        echo "<pre>" . $e . "</pre>";
    }
    else {
        $content = file_get_contents(DIR_ROOT . '/app/Views/errors/500.php');
        $response = new Response($content, 500, ['Content-Type' => 'text/html']);
        $response->send();
    }
});

# La seule instance de RenderService
$renderService = new RenderService();

# La seule instance du container d'injection des dépendances
$diContainer = new DIContainer($renderService);

# Router
if (isset($routes)) {
    $router = $diContainer->getRouter($routes, $diContainer); # $routes dans config.php
} else {
    throw new ServerException("FATAL ERROR: bootstrap.php Routes indéfinies.");
}