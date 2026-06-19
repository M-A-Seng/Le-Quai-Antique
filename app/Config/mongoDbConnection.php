<?php

namespace App\Config;

use MongoDB\Client;
use MongoDB\Database;
use function App\Helpers\get_valid_env;

class mongoDbConnection
{
    private static ?Database $db = null;

    public static function getDatabase(): Database
    {
        if (self::$db === null) {
            $uri = get_valid_env('MONGODB_URL');
            $dbName = get_valid_env('MONGODB_NAME');
            $client = new Client($uri);
            self::$db = $client->selectDatabase($dbName);
        }
        return self::$db;
    }
}