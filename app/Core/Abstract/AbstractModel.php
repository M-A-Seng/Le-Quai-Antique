<?php

namespace App\Core\Abstract;

use App\Config\DbConnection;
use App\Exceptions\DataProcessingException;
use App\Exceptions\DbFailureException;
use App\Exceptions\InvalidArrayForDbException;
use App\Services\ConstantsCheckerService;
use PDO;
use PDOException;

/**
 * AbstractModel implémente CRUD et étend AbstractCheckersModel.
 * 
 * - checkProtectedColumns()
 * - insert()
 * - findAll()
 * - findBy()
 * - update()
 * - delete()
 */
abstract class AbstractModel extends ConstantsCheckerService
{
    protected PDO $pdo;
    protected const TABLE="";
    protected const ALLOWED_COLUMNS=[];
 
    /**
     * __construct prend en paramètre une instance de la classe DbConnection. Il vérifie également que les constantes sont définies.
     *
     * @param DbConnection $connection
     * @return void
     */
    public function __construct (DbConnection $connection)
    {
        $constantsToCheck = ['ALLOWED_COLUMNS' => 'array', 'TABLE' => 'string']; 
        $this->validateConstants($constantsToCheck);
        $this->pdo = $connection->getConnection();
    }

    /**
     * filterAllowedColumns vérifie que les données envoyées figurent dans la whitelist des colonnes.
     * 
     * Assurez-vous que la constante ALLOWED_COLUMNS est correctement définie dans la classe où filterAllowedColumns est appelée.
     *
     * @param  array|string $data
     * @return array|string
     */
    private function filterAllowedColumns(string $className, array|string $data): array|string
    {
        if (empty($data)) {
            throw new InvalidArrayForDbException(__METHOD__ . "Le deuxième argument est vide: Au moins une colonne est attendue.");
        }
        $columns = is_string($data) ? [$data] : (array_is_list($data) ? $data : array_keys($data));

        $columns = array_map('strtolower', $columns);
        $allowedColumns = array_map('strtolower', $className::ALLOWED_COLUMNS);

        $unknownColumns = array_diff($columns, $allowedColumns);

        if (!empty($unknownColumns)) {
            throw new InvalidArrayForDbException(
                __METHOD__ . 'Colonnes inconnues ou invalides: ' . implode(', ', $unknownColumns)
            );
        }

        return $data;
    }
    
    /**
     * checkProtectedColumns vérifie que les données envoyées ne touchent pas aux colonnes spécifiées. Par exemple pour les colonnes accessibles uniquement en "read only".
     *
     * @param  array $data
     * @param  array $protectedColumns
     * @return void
     */
    protected function checkProtectedColumns(array $data, array $protectedColumns): void
    {
        $forbiddenColumns = array_intersect(array_keys($data), $protectedColumns);

        if (!empty($forbiddenColumns)) {
            throw new InvalidArrayForDbException(__METHOD__ . "Accès refusé pour les colonnes : " . implode(", ", $forbiddenColumns));
        }
    }

    /**
     * insert ajoute des données à une table.
     *
     * @param  array $data
     * @return array
     */
    protected function insert(array $data): array
    {
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . "Tableau associatif attendu en paramètre de insert().");
        }
        $data = $this->filterAllowedColumns(static::class, $data);

        $columns = implode(',', array_map(fn($col) => "\"$col\"", array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO \"" . static::TABLE . "\" ($columns) VALUES ($placeholders) RETURNING *";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute($data);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération insert(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findAll Retourne tous les enregistrements de la table définit dans la constante TABLE.
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
            throw new DbFailureException(__METHOD__ . "Echec de l'opération findAll(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findBy recupère tous les enregistrements d'une table correspondant aux conditions données pour la clause WHERE.
     *
     * @param  array $conditions | ['column' => 'value']
     * @return array
     */
    protected function findBy(array $conditions): array
    {
        if (empty($conditions) || array_is_list($conditions)) {
            throw new DataProcessingException(__METHOD__ . "Un tableau associatif est attendu en paramètre de findBy().");
        }

        $sql = "SELECT * FROM " . static::TABLE . " WHERE ";
        $clauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $this->filterAllowedColumns(static::class, $column);

            if ($value === null) {
                $clauses[] = "$column IS NULL";
            } else {
                $paramName = "value_" . $column;
                $clauses[] = "$column = :$paramName";
                $params[$paramName] = $value;
            }
        }
        $sql .= implode(" AND ", $clauses);

        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération findBy(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * update met à jour les données d'un enregistrement.
     *
     * @param  int $id
     * @param  array $data
     * @return array
     */
    protected function update(int $id, array $data): array
    {
        if (empty($data) || empty($id)) {
            throw new DataProcessingException(__METHOD__ . "Veuillez passer les arguments demandés en paramètre de update().");
        }
        if (array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . "Un tableau associatif est attentdu en deuxième paramètre de update().");
        }
        $this->filterAllowedColumns(static::class, array_keys($data));

        $processedData = [];
        foreach ($data as $column => $value) {
            $processedData[] = "\"$column\" = :$column";
        }
        $setClause = implode(', ', $processedData);

        $sql = "UPDATE \"" . static::TABLE . "\" SET $setClause WHERE id = :id RETURNING *";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;

        try {
            $stmt->execute($data);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Échec de l'opération update(): " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * delete supprime les données d'un enregistrement.
     *
     * @param  array $conditions | 'colomn' => 'value';
     * @return int
     */
    protected function delete(array $conditions): int
    {
        if (empty($conditions) || array_is_list($conditions)) {
            throw new DataProcessingException(__METHOD__ . 'Tableau associatif attendu en paramètre de delete().');
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
            throw new DbFailureException(__METHOD__ . "Echec de l'opération delete(): " . $e->getMessage(), 0, $e);
        }
    }
}

