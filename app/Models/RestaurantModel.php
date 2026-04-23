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
    # Constantes utilisées dans AbstractModel.
    protected const TABLE = "restaurant";
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_id",
        "siret",
        "name",
        "address",
        "tel",
        "admin_id",
    ];

    # Restaurant unique à l'heure actuelle avec données non modifiables (cahier des charges).
    private $readOnlyColumns = [
        "id",
        "restaurant_id",
        "siret",
        "name",
        "address",
        "tel",
        "admin_id",
    ];
    
    /**
     * getRestaurantByAdmin trouve le restaurant associé au compte administrateur.
     * 
     * Exception si non trouvé.
     * 
     * NOTE: Fonctionne que si l'administrateur est associé qu'à un seul restaurant.
     *
     * @param  int $adminId
     * @return array
     */
    public function getRestaurantByAdmin(int $adminId): array
    {
        $restaurant = $this->findBy(['admin_id' => $adminId]);
        if (empty($restaurant)) {
            throw new NotFoundException(message: __METHOD__ . ": Administrateur non reconnu.");
        }
        return $restaurant[0];
    }
    
    /**
     * getRestaurantById retourne les données du restaurant correspondant à l'id.
     * 
     * Exception si non trouvé.
     *
     * @param  int $id
     * @return array
     */
    public function getRestaurantById(int $id): array
    {
        $result = $this->findBy(['id' => $id]);
        if (empty($result)) {
            throw new NotFoundException(message: __METHOD__ . ": Restaurant non trouvé dans la db.");
        }
        return $result[0];
    }
    
    # La modification des données du restaurant n'est pas demandée dans le cahier des charges.
    // public function updateRestaurant(array $data): array
    // {
    //     $this->getRestaurantByAdmin($_SESSION['id']); 
    //     $this->checkProtectedColumns($data, $this->readOnlyColumns);
    //     return $this->update($_SESSION['id'], $data);
    // }
}