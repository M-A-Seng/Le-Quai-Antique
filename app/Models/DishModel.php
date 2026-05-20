<?php

namespace App\Models;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;

/**
 * DishModel
 * 
 * - findDishesInCategory()
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
     * findDishesInCategory retourne tous les enregistrements partageant la même catégorie dans un restaurant
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return ?array
     */
    public function findDishesInCategory(int $restaurantId, int $categoryId): ?array
    {
        $result = $this->findBy(['restaurant_id' => $restaurantId, 'category_id' => $categoryId]);
        return empty($result) ? null : $result;
    }
}