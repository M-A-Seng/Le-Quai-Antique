<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidFieldException;
use App\Models\CategoryModel;
use App\Models\RestaurantModel;
use Throwable;

/**
 * CategoryService
 * 
 * - newCategory()
 * - getRestaurantCategories()
 * - updateCategory()
 * - changeCategoriesOrder()
 * - getCategoryCount()
 * - removeCategory()
 */
class CategoryService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "position",
        "restaurant_id",
        "title",
    ];
    private array $categoryExpectedInput = [
        "id",
        "position",
        "title",
    ];

    public function __construct(private CategoryModel $categoryModel, 
                                private RestaurantModel $restaurantModel) {}
    
    /**
     * newCategory ajoute une nouvelle catégorie
     *
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     * 
     * @throws InvalidFieldException
     */
    public function newCategory(int $restaurantId, array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data)) {
            throw new InvalidFieldException("Veuillez renseigner les champs requis.");
        }
        $this->validatePositiveInteger($restaurantId);
        $data['position'] = $this->getCategoryCount($restaurantId) + 1;

        $this->checkExpectedKeys($this->categoryExpectedInput, $data, false);
        $data = [
            'restaurant_id' => $restaurantId,
            'position' => $data['position'],
            'title' => $data['title']
        ];
        $this->validateNotNullKeys(static::class, $data, true);
        return $this->categoryModel->createCategory($data);
    }
    
    /**
     * getRestaurantCategories récupère toutes les catégories du restaurant
     *
     * @param  int $restaurantId
     * @return ?array
     */
    public function getRestaurantCategories(int $restaurantId): ?array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);

        return $this->categoryModel->findRestaurantCategoriesById($restaurantId);
    }
    
    /**
     * updateCategory modifie une catégorie
     *
     * @param  array $data
     * @return array
     * 
     * @throws InvalidFieldException
     */
    public function updateCategory(array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data)) {
            throw new InvalidFieldException("Veuillez renseigner les champs requis.");
        }
        $this->checkExpectedKeys($this->categoryExpectedInput, $data, false);
        $this->validatePositiveInteger($data['id']);
        
        $this->validateNotNullKeys(static::class, ['title' => $data['title']]);
        return $this->categoryModel->updateCategory($data['id'], ['title' => $data['title']]);
    }
    
    /**
     * changeCategoriesOrder met à jour l'ordre des catégories dans un restaurant
     *
     * @param  array $data | liste des catégories
     * @return bool
     * 
     * @throws DataProcessingException
     * @throws Throwable
     */
    public function changeCategoriesOrder(array $data): bool
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (!array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Liste attendue en paramètre");
        }
        try {
            $this->categoryModel->beginTransaction();
            foreach ($data as $index => $id) {
                $this->categoryModel->updateCategory($id, ['position' => $index]);
            }
            $this->categoryModel->commit();
            return true;
        } 
        catch (Throwable $e) {
            $this->categoryModel->rollBack();
            throw $e;
        }
    }
    
    /**
     * getCategoryCount retourne le nombre total de catégories de plats
     *
     * @param  int $restaurantId
     * @return int
     */
    public function getCategoryCount(int $restaurantId): int
    {
        $this->validatePositiveInteger($restaurantId);
        $this->restaurantModel->getRestaurantById($restaurantId);
        return $this->categoryModel->countCategories($restaurantId);
    }
    
    /**
     * removeCategory supprime une catégorie
     *
     * @param  int $categoryId
     * @return int
     */
    public function removeCategory(int $categoryId): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($categoryId);
        $this->categoryModel->findCategoryById($categoryId);

        return $this->categoryModel->deleteCategory($categoryId);
    }
}