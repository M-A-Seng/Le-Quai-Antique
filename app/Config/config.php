<?php 

namespace App\Config;

# Charger automatiquement les "require" avec les spacenames et use
require __DIR__ . '/../../vendor/autoload.php';

# Routes de l'application
$routes = require 'routes.php';

# Instances PDO
use App\Config\DbConnection;

$frontConnection = new DbConnection('front');
$backConnection = new DbConnection('back');
$logsConnection = new DbConnection('logs');