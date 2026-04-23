<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Models\RestaurantModel;

/**
 * RestaurantService implémente les opérations de gestion des horraires et du nombre de convives du restaurant.
 * 
 * - updateRestaurantServices()
 * - getRestaurant()
 */
class RestaurantService extends AbstractService
{
    protected const NOT_NULL_COLUMNS = [
        "siret",
        "name",
        "address",
    ];
    
    public function __construct(private RestaurantModel $restaurantModel) {}
        
    /**
     * getRestaurant retourne le restaurant associé à l'administrateur connecté.
     * 
     * Note: Il n'y a actuellement que 1 restaurant/compte administrateur.
     *
     * @return array
     */
    public function getRestaurantByAdmin()
    {
        $adminId = $_SESSION['id'] ?? 0;
        return $this->restaurantModel->getRestaurantByAdmin((int)$adminId);
    }
    
    /**
     * getRestaurantById retourne les données du restaurant correspondant à l'id.
     *
     * @return array
     */
    public function getRestaurantById(int $id): array
    {
        return $this->restaurantModel->getRestaurantById((int)$id);
    }
}