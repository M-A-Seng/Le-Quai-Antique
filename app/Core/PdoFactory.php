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
    public function create(string $dsn, string $user, string $password): PDO
    {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}