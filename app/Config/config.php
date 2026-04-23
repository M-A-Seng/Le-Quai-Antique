<?php 

namespace App\Config;

# Variable globale, chemin racine du projet
define('DIR_ROOT', dirname(__DIR__, 2));

# Variable globale, environnement dev ou prod
define('APPENV', getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? null));

# Chargement autmatique des dépendances dans les fichers php
require_once DIR_ROOT . '/vendor/autoload.php';

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
    'secure' => false,   // Mettre true en prod
    'samesite' => 'Strict'
]);
