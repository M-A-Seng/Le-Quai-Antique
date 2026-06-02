<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidFieldException;
use App\Models\SetMenuModel;
use Throwable;

/**
 * SetMenuService
 * 
 * - newMenu()
 * - getRestaurantMenus()
 * - ModifyMenu()
 * - changeMenuOrder()
 * - getMenuCount()
 * - removeMenu()
 */
class SetMenuService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "position",
        "restaurant_id",
        "title",
        "description",
        "price"
    ];
    private const AUTHORIZED_KEYS = [
        "id",
        "position",
        "restaurant_id",
        "title",
        "description",
        "price"
    ];

    public function __construct(private SetMenuModel $setMenuModel) {}
    
    /**
     * newMenu ajouter menu
     *
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     * 
     * @throws InvalidFieldException
     */
    public function newMenu(int $restaurantId, array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data)) {
            throw new InvalidFieldException("Veuillez renseigner les champs requis.");
        }
        $this->validatePositiveInteger($restaurantId);
        $this->checkExpectedKeys(self::AUTHORIZED_KEYS, $data);

        $table = [
            "position" => $this->getMenuCount($restaurantId) + 1,
            "restaurant_id" => $restaurantId,
            "title" => $data['title'],
            "description" => $data['description'],
            "price" => $this->priceCheckAndNormalize($data['price'])
        ];
        $this->validateNotNullKeys(static::class, $table, true);
        return $this->setMenuModel->createMenu($table);
    }
    
    /**
     * getRestaurantMenus retourne tous les menus
     *
     * @param  int $restaurantId
     * @return array
     */
    public function getRestaurantMenus(int $restaurantId): ?array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);

        return $this->setMenuModel->findAllMenus($restaurantId);
    }
    
    /**
     * ModifyMenu modifier un menu
     *
     * @param  array $data
     * @return array
     * 
     * @throws InvalidFieldException
     */
    public function ModifyMenu(array $data): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (empty($data)) {
            throw new InvalidFieldException("Veuillez renseigner les champs requis.");
        }
        $this->checkExpectedKeys(self::AUTHORIZED_KEYS, $data);
        $this->validatePositiveInteger($data['id']);
        $this->setMenuModel->findMenuById($data['id']);
        $table = [
            "title" => $data['title'],
            "description" => $data['description'],
            "price" => $this->priceCheckAndNormalize($data['price'])
        ];
        $this->validateNotNullKeys(static::class, $table);
        return $this->setMenuModel->updateMenu($data['id'], $table);
    }
    
    /**
     * changeMenuOrder modifier l'ordre des menus
     *
     * @param  array $data
     * @return bool
     * 
     * @throws DataProcessingException
     * @throws Throwable
     */
    public function changeMenuOrder(array $data): bool
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        if (!array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Liste attendue en paramètre");
        }
        try {
            $this->setMenuModel->beginTransaction();
            foreach ($data as $index => $id) {
                $this->setMenuModel->updateMenu($id, ['position' => $index]);
            }
            $this->setMenuModel->commit();
            return true;
        } 
        catch (Throwable $e) {
            $this->setMenuModel->rollBack();
            throw $e;
        }
    }
    
    /**
     * getMenuCount compter nombre de menu
     *
     * @param  int $restaurantId
     * @return int
     */
    private function getMenuCount(int $restaurantId): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($restaurantId);

        return $this->setMenuModel->countMenus($restaurantId);
    }
    
    /**
     * removeMenu retirer menu
     *
     * @param  int $menuId
     * @return int
     */
    public function removeMenu(int $menuId): int
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        $this->validatePositiveInteger($menuId);
        $this->setMenuModel->findMenuById($menuId);

        return $this->setMenuModel->deleteMenu($menuId);
    }
}