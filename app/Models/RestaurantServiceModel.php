<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Enums\DayOfWeek;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDO;
use PDOException;

/**
 * RestaurantServiceModel
 * 
 * - findRestaurantServicesByRestaurantId()
 * - findRestaurantServiceByServiceId()
 * - findRestaurantServicesByOpeningDay()
 * - findRestaurantServiceByTime()
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

    private array $readOnlyColumns = [
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
    public function findRestaurantServicesByRestaurantId(int $id): array
    {
        $sql = "SELECT 
                    id,
                    service_type,
                    service_duration,
                    opening_time,
                    closing_time,
                    max_guests 
                FROM restaurant_service 
                WHERE restaurant_id = :restaurant_id 
                ORDER BY service_type;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
     * findRestaurantServicesByOpeningDay retourne tous les services actif pour 1 jour de la semaine du restaurant donné.
     * 
     * @param  int $restaurantId
     * @param  DayOfWeek $dayOfWeek
     * @return ?array
     */
    public function findRestaurantServicesByOpeningDay(int $restaurantId, DayOfWeek $dayOfWeek): ?array
    {
        $sql = 
            "SELECT 
                rs.id, 
                rs.service_type, 
                rs.restaurant_id, 
                rs.service_duration, 
                rs.opening_time, 
                rs.closing_time,
                rs.max_guests 
            FROM app_back.restaurant_service rs
            JOIN app_back.restaurant_service_day rsd
                ON rsd.restaurant_service_id = rs.id
            WHERE rs.restaurant_id = :restaurant_id
            AND rsd.day_of_week = :day_of_week
            ORDER BY rs.service_type, rs.opening_time;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId, 'day_of_week' => $dayOfWeek->value]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return !empty($result) ? $result : null;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
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
    public function findRestaurantServiceByServiceId(int $id): array
    {
        $result = $this->findBy(["id" => $id]);
        if (empty($result)) {
            Throw new NotFoundException(message:__METHOD__ . ": Service '$id' non trouvé dans la db.", UIMessage:"Service non trouvé.");
        }
        return $result[0];
    }
    
    /**
     * findRestaurantServiceByTime retourne le service dont la plage horaire inclue l'heure donnée.
     *
     * @param  int $restaurantId
     * @param  string $time
     * @return ?array
     */
    public function findRestaurantServiceByTime(int $restaurantId, string $time): ?array
    {
        $sql = "SELECT *
                FROM restaurant_service
                WHERE restaurant_id = :restaurant_id
                AND (
                    -- horaire normal
                    (opening_time <= closing_time AND :time BETWEEN opening_time AND closing_time)
                    OR
                    -- dépassement de minuit
                    (opening_time > closing_time AND (:time >= opening_time OR :time <= closing_time))
                );";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId, 'time' => $time]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($result) ? $result : null;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * updateRestaurantService met à jour 1 seul service de 1 restaurant à la fois.
     * 
     * Exception si invalide.
     *
     * @param  int $serviceId
     * @param  array $data
     * @return array
     */
    public function updateRestaurantService(int $serviceId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->findRestaurantServiceByServiceId($serviceId); # service existe?
        
        return $this->update($serviceId, $data);
    }

    # La création/suppression de services de restauration n'est pas demandée dans le cahier des charges.
    // public function createRestaurantService(array $data): array {}
    // public function deleteRestaurantService(int $serviceId, int $restaurantId): void {}
}