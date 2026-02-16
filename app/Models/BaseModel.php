<?php

namespace App\Models;

use App\Config\DbConnection;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct(DbConnection $connection)
    {
        $this->pdo = $connection->getConnection();
    }
}
