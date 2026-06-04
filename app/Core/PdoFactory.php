<?php

namespace App\Core;

use PDO;

class PdoFactory
{
    /**
     * create
     *
     * @param  string $dsn
     * @param  string $user
     * @param  string $password
     * @return PDO
     */
    public function createFromDsn(string $dsn, string $user, string $password): PDO
    {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT => 5
        ]);
    }
    
    /**
     * createFromUrl
     *
     * @param  string $url
     * @param  string $dbUser
     * @param  string $userPass
     * @return PDO
     */
    public function createFromUrl(string $url, string $dbUser, string $userPass): PDO
    {
        $url = parse_url($url);
        
        $dsn = "pgsql:"
            . "host=" . $url["host"] . ";"
            . "port=5432;"
            . "dbname=" . ltrim($url["path"], "/")
            . ";sslmode=require";

        return new PDO($dsn, $dbUser, $userPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]);
    }
}