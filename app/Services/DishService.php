<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Models\DishModel;
use App\Models\RestaurantModel;

/**
 * DishService
 * 
 * - getDishesInCategory()
 */
class DishService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "position",
        "restaurant_id",
        "category_id",
        "title",
        "description",
        "price",
    ];
    private array $dishExpectedInput = [
        "position",
        "restaurant_id",
        "category_id",
        "title",
        "description",
        "price",
    ];

    public function __construct(private DishModel $dishModel, private RestaurantModel $restaurantModel) {}
    
    /**
     * getDishesInCategory récupère tous les plats d'une catégorie spécifiée
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return array
     */
    public function getDishesInCategory(int $restaurantId, int $categoryId): ?array
    {
        $this->validatePositiveInteger($restaurantId);
        $this->restaurantModel->getRestaurantById($restaurantId);
        $this->validatePositiveInteger($categoryId);
        $result = $this->dishModel->findDishesInCategory($restaurantId, $categoryId);

        if ($result) {
            foreach ($result as $row) {
                $data[] = $row['title'];
            }
        }
        return empty($data) ? null : $data;
    }
}