<?php 
use function App\Helpers\get_valid_env;

# Variable globale, chemin racine du projet
define('DIR_ROOT', dirname(__DIR__, 2));

# Routes de l'application
$routes = require 'routes.php';

# TimeZone
date_default_timezone_set('Europe/Paris');

# Paramètres session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'httponly' => true,
    'secure' => get_valid_env('APP_ENV') !== 'dev',
    'samesite' => 'Strict'
]);
