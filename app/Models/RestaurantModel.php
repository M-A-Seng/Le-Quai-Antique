<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\NotFoundException;

/**
 * RestaurantModel
 * 
 * - getRestaurantByAdmin()
 * - updateRestaurant()
 */
class RestaurantModel extends AbstractModel
{
    # Les constantes sont utilisées dans AbstractModel.
    protected const ALLOWED_TABLES = ["restaurant"];
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_id",
        "name",
        "address",
        "tel",
        "admin_id",
        "lunch_opening_time",
        "lunch_closing_time",
        "lunch_max_guests",
        "evening_opening_time",
        "evening_closing_time",
        "evening_max_guests",
        "service_duration"
    ];
    protected const TABLE = "restaurant";

    private $readOnlyColumns = [
        "id",
        "restaurant_id",
        "name",
        "address",
        "tel",
        "admin_id",
        "service_duration"
    ];
    
    /**
     * getRestaurantByAdmin trouve le restaurant associé au compte administrateur.
     * 
     * NOTE: Fonctionne que si l'administrateur n'est associé qu'à un seul restaurant.
     *
     * @param  int $adminId
     * @return void
     */
    public function getRestaurantByAdmin(int $adminId)
    {
        $result = $this->findBy('admin_id', $adminId);
        if (empty($result)) {
            throw new NotFoundException(message: "Restaurant non trouvé dans la db.");
        }
        return $result[0];
    }
    
    /**
     * updateRestaurant met à jour les horraires d'ouverture et le nombre de convives du restaurant.
     *
     * @param  array $data
     * @return void
     */
    public function updateRestaurant(array $data)
    {
        $this->getRestaurantByAdmin($_SESSION['id']); # Actuellement qu'un seul restaurant/admin
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        
        return $this->update($_SESSION['id'], $data);
    }
}