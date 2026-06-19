<?php

namespace App\Models;

use App\Config\mongoDbConnection;

class LogModel
{
    private $collection;

    public function __construct()
    {
        $db = mongoDbConnection::getDatabase();
        $this->collection = $db->selectCollection('logs');
    }

    public function insert(array $data): string
    {
        $result = $this->collection->insertOne($data);
        return (string) $result->getInsertedId(); // retourne l'ID
    }
}