<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Connection à la base de données postgresql
 */
class DbConnection
{
    private PDO $pdo;
    
    /**
     * __construct
     *
     * @param  string $userType
     * @return void
     */
    public function __construct(string $userType)
    {
        $host = $_ENV['DB_HOST'] ?? throw new \Exception('DB Host manquant');
        $dbName = $_ENV['DB_NAME'] ?? throw new \Exception('DB Name manquant');

        $users = [
            'front' => [
                'user' => $_ENV['DB_USER_FRONT'],
                'password' => $_ENV['DB_PASS_FRONT']
            ],
            'back' => [
                'user' => $_ENV['DB_USER_BACK'],
                'password' => $_ENV['DB_PASS_BACK']
            ],
            'logs' => [
                'user' => $_ENV['DB_USER_LOGS'],
                'password' => $_ENV['DB_PASS_LOGS']
            ]
        ];

        if (!isset($users[$userType])) {
            throw new \Exception("Utilisateur DB non valide");
        }

        $user = $users[$userType]['user'];
        $password = $users[$userType]['password'];

        try {
            $dsn = "pgsql:host=$host;dbname=$dbName";
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } 
        catch (PDOException $e) {
            error_log($e->getMessage());
            throw new PDOException("Erreur de connexion à la base de données.");
        }
    }
    
    /**
     * getConnection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}