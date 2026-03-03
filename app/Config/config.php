<?php 

namespace App\Config;

# Charger automatiquement les chemins dans les namespaces avec use
require __DIR__ . '/../../vendor/autoload.php';

# Routes de l'application
$routes = require 'routes.php';

# Gestion des exceptions uncatched
use Throwable;

set_exception_handler(function (Throwable $e) 
{
    http_response_code(500);

    $logMessage = "[" . date('Y-m-d H:i:s') . "] "
                . $e->getMessage() . " in "
                . $e->getFile() . ":" . $e->getLine() . "\n"
                . $e->getTraceAsString() . "\n\n";

    error_log($logMessage, 3, __DIR__ . '/../../logs/errors.log');

    echo "Une erreur interne est survenue.";

    // Environnement dev
    echo "<pre>" . $e . "</pre>";
});