<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\DataProcessingException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDO;
use PDOException;

/**
 * RestaurantServiceModel
 * 
 * - getRestaurantServicesByRestaurantId()
 * - getRestaurantServiceByServiceId()
 * - updateRestaurantService()
 */
class RestaurantServiceModel extends AbstractModel
{
    # Constantes utilisées dans AbstractModel.
    protected const TABLE = "restaurant_service";
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_id",
        "service_type",
        "service_duration",
        "opening_time",
        "closing_time",
        "max_guests",
    ];

    private $readOnlyColumns = [
        "id",
        "restaurant_id",
    ];
    
    /**
     * getRestaurantServicesByRestaurantId retourne les données de tous les services du restaurant donné.
     * 
     * Exception si non trouvé.
     *
     * @param  int $id
     * @return array
     */
    public function getRestaurantServicesByRestaurantId(int $id): array
    {
        if (empty($id)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez entrer l'id du restaurant en paramètre.");
        }
        $sql = "SELECT 
                    service_type,
                    id,
                    service_duration,
                    opening_time,
                    closing_time,
                    max_guests 
                FROM restaurant_service 
                WHERE restaurant_id = :restaurant_id 
                ORDER BY service_type;";

        $stmt = $this->pdo->prepare($sql);
        $data['restaurant_id'] = $id;
        try {
            $stmt->execute($data);
            $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            if (empty($result)) {
                throw new NotFoundException(message:__METHOD__ . ": Pas de service trouvé en db pour le restaurant '$id'.", UIMessage:"Service non trouvé.");
            }
            return $result;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . ": Échec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * getRestaurantServiceByServiceId retourne les données de 1 seul service.
     * 
     * Exception si non trouvé.
     *
     * @param  int $id
     * @return array
     */
    public function getRestaurantServiceByServiceId(int $id): array
    {
        $result = $this->findBy(["id" => $id]);
        if (empty($result)) {
            Throw new NotFoundException(message:__METHOD__ . ": Service '$id' non trouvé dans la db.", UIMessage:"Service non trouvé.");
        }
        return $result[0];
    }
    
    /**
     * updateRestaurantService met à jour 1 seul service de 1 restaurant à la fois.
     * 
     * Exception si invalide.
     *
     * @param  int $serviceId
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     */
    public function updateRestaurantService(int $serviceId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->getRestaurantServiceByServiceId($serviceId); # Vérifier qu'il n'y a pas de not found
        
        return $this->update($serviceId, $data);
    }

    # La création/suppression de services de restauration n'est pas demandée dans le cahier des charges.
    // public function createRestaurantService(array $data): array {}
    // public function deleteRestaurantService(int $serviceId, int $restaurantId): void {}
}