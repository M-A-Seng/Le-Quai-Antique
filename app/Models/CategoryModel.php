<?php

namespace App\Models;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDOException;

/**
 * CategoryModel
 * 
 * - createCategory()
 * - findRestaurantCategoriesById()
 * - findCategoryById()
 * - countCategories()
 * - updateCategory()
 * - deleteCategory()
 */
class CategoryModel extends AbstractModel
{
    protected const TABLE = "dish_category";
    protected const ALLOWED_COLUMNS = [
        "id",
        "position",
        "restaurant_id",
        "title",
        "created_at",
        "updated_at",
    ];

    private $readOnlyColumns = [
        "id",
        "created_at",
        "updated_at"
    ];

    public function __construct(DbConnection $connection)
    {
        parent::__construct($connection);
    }
    
    /**
     * createCategory ajoute une nouvelle catégorie
     *
     * @param  array $data
     * @return array
     */
    public function createCategory(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }
    
    /**
     * findCategoriesByRestaurantId trouve les catégories d'un restaurant
     *
     * @param  int $id
     * @return ?array
     */
    public function findRestaurantCategoriesById(int $id): ?array
    {
        $result = $this->findBy(['restaurant_id' => $id], ['position' => 'ASC']);
        return empty($result) ? null : $result;
    }
    
    /**
     * FindCategoryById retourne la catégorie recherchée
     *
     * @param  int $id
     * @return array
     * 
     * @throws NotFoundException
     */
    public function findCategoryById(int $id): array
    {
        $result = $this->findBy(['id' => $id]);
        if (empty($result)) {
            throw new NotFoundException(__METHOD__ . ": Aucune catégorie ne correspond à l'id $id");
        }
        return $result[0];
    }
    
    /**
     * countCategories retourne le nombre total de catégories de plats
     *
     * @param  int $restaurantId
     * @return int
     */
    public function countCategories(int $restaurantId): int
    {
        $sql = "SELECT COUNT(*) AS total_categories
                FROM dish_category
                WHERE restaurant_id = :restaurant_id;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId]);
            return (int)$stmt->fetchColumn();
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * updateCategory modifie une catégorie
     *
     * @param  int $categoryId
     * @param  array $data
     * @return array
     */
    public function updateCategory(int $categoryId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->update($categoryId, $data);
    }
    
    /**
     * deleteCategory
     *
     * @param  int $categoryId
     * @return int
     */
    public function deleteCategory(int $categoryId): int
    {
        return $this->delete(['id' => $categoryId]);
    }
}