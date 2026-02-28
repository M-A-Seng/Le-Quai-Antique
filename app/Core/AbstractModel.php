<?php

namespace App\Core;

use App\Config\DbConnection;
use App\Core\AbstractCheckersModel;
use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

/**
 * AbstractModel implémente CRUD et étend AbstractCheckersModel.
 */
abstract class AbstractModel extends AbstractCheckersModel
{
    protected PDO $pdo;
    protected const TABLE="";
 
    /**
     * __construct prend en paramètre une instance de la classe DbConnection. Il vérifie également que les constantes sont définies.
     *
     * @param  mixed $connection
     * @return void
     */
    public function __construct (DbConnection $connection)
    {
        $constantsToCheck = array_merge(
            $this->getParentConstants(), ['TABLE' => 'is_string']
        );
        $this->validateConstants(static::class, $constantsToCheck);

        $this->filterAllowedTables(static::class, static::TABLE);
        $this->pdo = $connection->getConnection();
    }
        
    /**
     * insert ajoute des données à une table.
     *
     * @param  array $data
     * @return int
     */
    protected function insert(array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Tableau associatif attendu en paramètre.");
        }
        $data = $this->filterAllowedColumns(static::class, $data);

        $columns = implode(',', array_map(fn($col) => "\"$col\"", array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO \"" . static::TABLE . "\" ($columns) VALUES ($placeholders) RETURNING id";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute($data);
            return (int) $stmt->fetchColumn();
        } 
        catch (PDOException $e) {
            throw new RuntimeException("Echec de l'opération insert(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findAll récupère tous les enregistrements d'une table.
     *
     * @return array
     */
    protected function findAll(): array
    {
        $sql = "SELECT * FROM \"" . static::TABLE . "\"";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            throw new RuntimeException("Echec de l'opération findAll(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findBy recupère tous les enregistrements d'une table contenant la valeur donnée.
     *
     * @param  string $column
     * @param  mixed $value
     * @return array
     */
    protected function findBy(string $column, mixed $value): array
    {
        $this->filterAllowedColumns(static::class, $column);

        $sql = "SELECT * FROM \"" . static::TABLE . "\" WHERE \"$column\" = :value";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute(['value' => $value]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            throw new RuntimeException("Echec de l'opération findBy(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * update met à jour les données d'un enregistrement.
     *
     * @param  int $id
     * @param  array $data
     * @return int
     */
    protected function update(int $id, array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Tableau associatif attendu en deuxième paramètre.');
        }
        $this->filterAllowedColumns(static::class, array_keys($data));

        $processedData = [];
        foreach ($data as $column => $value) {
            $processedData[] = "\"$column\" = :$column";
        }
        $setClause = implode(', ', $processedData);

        $sql = "UPDATE \"" . static::TABLE . "\" SET $setClause WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;

        try {
            $stmt->execute($data);
            return $stmt->rowCount();
        } 
        catch (PDOException $e) {
            throw new RuntimeException("Échec de l'opération update(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * delete supprime les données d'un enregistrement.
     *
     * @param  array $conditions
     * @return int
     */
    protected function delete(array $conditions): int
    {
        if (empty($conditions)) {
            throw new InvalidArgumentException('Tableau associatif attendu en paramètre.');
        }
        $this->filterAllowedColumns(static::class, array_keys($conditions));

        $processedConditions = [];
        foreach ($conditions as $column => $value) {
            $processedConditions[] = "\"$column\" = :$column";
        }
        $whereClause = implode(' AND ', $processedConditions);

        $sql = "DELETE FROM \"" . static::TABLE . "\" WHERE $whereClause";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute($conditions);
            return $stmt->rowCount();
        } 
        catch (PDOException $e) {
            throw new RuntimeException("Echec de l'opération delete(): " . $e->getMessage(), 0, $e);
        }
    }
}

