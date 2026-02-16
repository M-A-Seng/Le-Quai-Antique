<?php 

namespace App\Config;

# Charger automatiquement les "require" en fonction des 'spacenames' & 'use'
require __DIR__ . '/../../vendor/autoload.php';

# Routes de l'application
$routes = require 'routes.php';

# Charger automatiqument les variables d'environnement du fichier .env
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();