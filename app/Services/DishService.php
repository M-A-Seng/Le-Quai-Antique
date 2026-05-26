<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Models\CategoryModel;
use App\Models\DishModel;
use App\Models\RestaurantModel;
use Throwable;

/**
 * DishService
 * 
 * - newDish()
 * - getDishesInCategory()
 * - getAllRestaurantDishes()
 * - getDishCount()
 * - modifyDish()
 * - changeDishesOrder()
 * - deleteDish()
 */
class DishService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "position",
        "restaurant_id",
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

    public function __construct(private DishModel $dishModel, 
                                private CategoryModel $categoryModel) {}
        
    /**
     * newDish ajoute un nouveau plat
     *
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     * 
     * @throws DataProcessingException
     */
    public function newDish(int $restaurantId, array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        $this->validatePositiveInteger($restaurantId);
        $this->checkExpectedKeys($this->dishExpectedInput, $data);

        $table = [
            "position" => $this->getDishCount($restaurantId) + 1,
            "restaurant_id" => $restaurantId,
            "title" => $data['title'],
            "description" => $data['description'],
            "price" => $this->priceCheckAndNormalize($data['price']),
        ];
        if (isset($data['category_id'])) {
            if (empty($data['category_id'])) {
                $data['category_id'] = null;
            } else {
                $this->validatePositiveInteger($data['category_id']);
                $this->categoryModel->findCategoryById($data['category_id']);
            }
            $table = array_merge($table, ['category_id' => $data['category_id']]);
        }
        $this->validateNotNullKeys(static::class, $table, true);
        return $this->dishModel->createDish($table);
    }

    /**
     * getDishesNamesInCategory retourne la liste des plats d'une catégorie
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return array|null
     */
    public function getDishesNamesInCategory(int $restaurantId, int $categoryId): ?array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);
        $this->validatePositiveInteger($categoryId);
        $result = $this->dishModel->findDishesInCategory($restaurantId, $categoryId);

        if ($result) {
            foreach ($result as $row) {
                $data[] = $row['title'];
            }
        }
        return empty($data) ? null : $data;
    }
    
    /**
     * getAllRestaurantDishes retourne tous les plats groupés par catégorie
     *
     * @param  int $restaurantId
     * @return array|null
     */
    public function getRestaurantDishes(int $restaurantId): ?array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);

        return $this->dishModel->findAllRestaurantDishes($restaurantId);
    }
    
    /**
     * getDishCount retourne le nombre de plats
     *
     * @param  int $restaurantId
     * @param  int $categoryId
     * @return int
     */
    private function getDishCount(int $restaurantId, ?int $categoryId = null): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);
        if ($categoryId !== null) {
            $this->validatePositiveInteger($categoryId);
        }
        return $this->dishModel->countDishes($restaurantId, $categoryId);
    }
    
    /**
     * modifyDish modifier plat
     *
     * @param  array $data
     * @return array
     * 
     * @throws DataProcessingException
     */
    public function modifyDish(array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        $this->validatePositiveInteger($data['id']);
        $this->dishModel->findDishById($data['id']);

        if (empty($data['category_id'])) {
            $data['category_id'] = null;
        } else {
            $this->validatePositiveInteger($data['category_id']);
            $this->categoryModel->findCategoryById($data['category_id']);
        }
        $table = [
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'price' => $this->priceCheckAndNormalize($data['price'])
        ];
        return $this->dishModel->updateDish($data['id'], $table);
    }
    
    /**
     * changeDishesOrder mofidier l'ordre des plats
     *
     * @param  array $data
     * @return bool
     * 
     * @throws DataProcessingException
     */
    public function changeDishesOrder(array $data): bool
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (!array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Liste attendue en paramètre");
        }
        try {
            $this->dishModel->beginTransaction();
            foreach ($data as $index => $id) {
                $this->dishModel->updateDish($id, ['position' => $index]);
            }
            $this->dishModel->commit();
            return true;
        } 
        catch (Throwable $e) {
            $this->dishModel->rollBack();
            throw $e;
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
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($dishId);
        $this->dishModel->findDishById($dishId);

        return $this->dishModel->deleteDish($dishId);
    }
}