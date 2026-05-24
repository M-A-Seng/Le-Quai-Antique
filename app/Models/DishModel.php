<?php

namespace App\Models;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDO;
use PDOException;

/**
 * DishModel
 * 
 * - createDish()
 * - findDishesInCategory()
 * - findAllRestaurantDishes()
 * - findDishById()
 * - updateDish()
 * - countDishes()
 * - deleteDish()
 */
class DishModel extends AbstractModel
{
    protected const TABLE = "dish";
    protected const ALLOWED_COLUMNS = [
        "id",
        "position",
        "restaurant_id",
        "category_id",
        "title",
        "description",
        "price",
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
     * createDish ajouter un nouveau plat
     *
     * @param  array $data
     * @return array
     */
    public function createDish(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }

    /**
     * findDishesInCategory retourne tous les enregistrements partageant la même catégorie dans un restaurant
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return null|array
     */
    public function findDishesInCategory(int $restaurantId, int $categoryId): ?array
    {
        $result = $this->findBy(['restaurant_id' => $restaurantId, 'category_id' => $categoryId]);
        return empty($result) ? null : $result;
    }
    
    /**
     * findAllRestaurantDishes retourne tous les plats groupés par catégorie
     *
     * @param  int $restaurantId
     * @return array|null
     * 
     * @throws DbFailureException
     */
    public function findAllRestaurantDishes(int $restaurantId): ?array
    {
        $sql = "SELECT
                    dc.id AS category_id,
                    dc.title AS category_title,
                    dc.position AS category_position,
                    d.id AS dish_id,
                    d.title,
                    d.description,
                    d.price,
                    d.position AS dish_position
                FROM app_back.dish d
                LEFT JOIN app_back.dish_category dc ON dc.id = d.category_id
                WHERE d.restaurant_id = :restaurant_id
                ORDER BY
                    dc.position NULLS LAST,
                    dc.title,
                    d.position NULLS LAST,
                    d.title;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return null;
            }
            $result = [];
            foreach ($rows as $row) {
                $category = $row['category_title'] ?? 'Assiettes non catégorisées';
                if (!isset($result[$category])) {
                    $result[$category] = [];
                }
                $result[$category][] = [
                    'id' => $row['dish_id'],
                    'category_id' => empty($row['category_id']) ? null : $row['category_id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'position' => $row['dish_position'],
                ];
            }
            return $result;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . ": Échec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findDishById trouver un plat
     *
     * @param  int $id
     * @return array
     * 
     * @throws NotFoundException
     */
    public function findDishById(int $id): ?array
    {
        $result = $this->findBy(['id' => $id]);
        if (empty($result)) {
            throw new NotFoundException(message:__METHOD__.": Plat '$id' inexistant.", UIMessage:"Plat inconnu.");
        }
        return $result[0];
    }
    
    /**
     * updateDish modifier plat
     *
     * @param  int $dishId
     * @param  array $data
     * @return array
     */
    public function updateDish(int $dishId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->update($dishId, $data);
    }
    
    /**
     * countDishes Compter le nombre de plats
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return int
     * 
     * @throws DbFailureException
     */
    public function countDishes(int $restaurantId, ?int $categoryId = null): int
    {
        $sql = "SELECT COUNT(*) AS total_dishes
                FROM dish
                WHERE restaurant_id = :restaurant_id
        ";
        $params = ['restaurant_id' => $restaurantId,];
        // catégorie optionnelle
        if ($categoryId !== null) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * deleteDish supprimer plat
     *
     * @param  int $dishId
     * @return int
     */
    public function deleteDish(int $dishId): int
    {
        return $this->delete(['id' => $dishId]);
    }
}