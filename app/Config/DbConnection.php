<?php

namespace App\Config;

use App\Core\Logger;
use App\Core\PdoFactory;
use App\Exceptions\DbFailureException;
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
    public function __construct(string $userType, private PdoFactory $pdoFactory, private Logger $logger)
    {
        $host = $_ENV['DB_HOST'] ?? throw new DbFailureException('DB Host manquant');
        $dbName = $_ENV['DB_NAME'] ?? throw new DbFailureException('DB Name manquant');
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
            throw new DbFailureException("Utilisateur DB non valide");
        }

        $dsn = "pgsql:host=$host;dbname=$dbName";
        $user = $users[$userType]['user'];
        $password = $users[$userType]['password'];

        try {
            $this->pdo = $this->pdoFactory->create($dsn, $user, $password);
        } 
        catch (PDOException $e) {
            $this->logger->dbError($e->getMessage());
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