<?php

namespace App\Models;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDOException;

class SetMenuModel extends AbstractModel
{
    protected const TABLE = "set_menu";
    protected const ALLOWED_COLUMNS = [
        "id",
        "position",
        "restaurant_id",
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

    public function createMenu(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }

    public function findAllMenus(int $restaurantId): ?array
    {
        $result = $this->findBy(['restaurant_id' => $restaurantId], ['position' => 'ASC']);
        return empty($result) ? null : $result;
    }

    public function findMenuById(int $id): array
    {
        $result = $this->findBy(['id' => $id]);
        if (empty($result)) {
            throw new NotFoundException(__METHOD__ . ": Aucun menu ne correspond à l'id $id");
        }
        return $result[0];
    }

    public function countMenus(int $restaurantId): int
    {
        $sql = "SELECT COUNT(*) AS total_menus
                FROM app_back.set_menu
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

    public function updateMenu(int $menuId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->update($menuId, $data);
    }

    public function deleteMenu(int $menuId): int
    {
        return $this->delete(['id' => $menuId]);
    }
}