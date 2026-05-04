<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDO;
use PDOException;

/**
 * ServiceModel
 * 
 * - createService()
 * - findServiceByTimestamptz()
 * - findServiceById()
 * - calculateRemainingPlacesInService()
 * - updateService()
 */
class ServiceModel extends AbstractModel
{
    # Constantes utilisées dans AbstractModel.
    protected const TABLE = "service";
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_id",
        "open_at",
        "close_at",
        "max_guests",
        "reservation_duration",
    ];

    private array $readOnlyColumns = [
        "id",
    ];
    
    /**
     * createService ajoute un nouveau service à la table service.
     *
     * @param  array $data
     * @return array
     */
    public function createService(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data); 
    }
    
    /**
     * findServiceByTimestamptz retourne le service correspondant à la date et l'heure donnée.
     *
     * @param  string $timestamptz | Y-m-d H:i:sP
     * @return ?array
     */
    public function findServiceByTimestamptz(int $restaurantId, string $timestamptz): ?array
    {
        $sql = "SELECT *
                FROM service
                WHERE restaurant_id = :restaurant_id 
                AND open_at <= :datetime
                AND close_at >= :datetime
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId, 'datetime' => $timestamptz]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($service) ? $service : null;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . ": Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * findServiceById retourne le service correspondant à l'ID.
     * 
     * Exception si non trouvé
     *
     * @param  int $id
     * @return array
     */
    public function findServiceById(int $id): array
    {
        $result = $this->findBy(["id" => $id]);
        if (empty($result)) {
            throw new NotFoundException(message: __METHOD__ . ": Service ID '$id' non trouvé en db.", UIMessage:"Service non trouvé.");
        }
        return $result[0];
    }
    
    /**
     * calculateRemainingPlacesInService retourne le nombre de places restantes dans un service.
     *
     * @param  int $serviceId
     * @return int
     */
    public function calculateRemainingPlacesInService(int $serviceId): int
    {
        $sql = "SELECT
                    s.max_guests - COALESCE(SUM(r.guest_count), 0) AS remaining_places
                FROM app_back.service s
                LEFT JOIN app_front.reservation r
                    ON r.service_id = s.id
                    AND r.status = 'CONFIRMED'
                WHERE s.id = :service_id
                GROUP BY s.id, s.max_guests;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['service_id' => $serviceId]);
            $remaining = $stmt->fetchColumn();
            return $remaining !== false ? (int)$remaining : 0;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Échec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * updateService met à jour un service.
     *
     * @param  int $id
     * @param  array $data
     * @return array
     */
    public function updateService(int $id, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->findServiceById($id);
        
        return $this->update($id, $data);
    }
}