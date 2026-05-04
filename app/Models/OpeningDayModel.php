<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Enums\DayOfWeek;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use PDO;
use PDOException;

/**
 * OpeningDayModel
 * 
 * - findOpeningDaysByRestaurantServiceId()
 * - findOpeningDaysByRestaurantId()
 */
class OpeningDayModel extends AbstractModel
{
    protected const TABLE = "restaurant_service_day";
    protected const ALLOWED_COLUMNS = [
        "id",
        "restaurant_service_id",
        "day_of_week",
    ];

    # Jours ouvrés non modifiables selon le cahier des charges
    private $readOnlyColumns = [
        "id",
        "restaurant_service_id",
        "day_of_week",
    ];
    
    /**
     * findOpeningDaysByRestaurantServiceId retourne les jours ouvrés de 1 service de restauration.
     *
     * @param int $restaurantServiceId
     * @param ?DayOfWeek $dayOfWeek | Pour chercher un jour précis
     * @return array
     */
    public function findOpeningDaysByRestaurantServiceId(int $restaurantServiceId, ?DayOfWeek $dayOfWeek = null): array
    {
        $data = ['restaurant_service_id' => $restaurantServiceId];
        if ($dayOfWeek) {
            $data['day_of_week'] = $dayOfWeek->value;
        }
        return $this->findBy($data);
    }
    
    /**
     * findRestaurantOpeningDaysByRestaurantId retourne tous les jours ouvrés du restaurant donné.
     * 
     * Exception si non trouvé
     *
     * @param  int $restaurantId
     * @return array
     */
    public function findOpeningDaysByRestaurantId(int $restaurantId): array
    {
        $sql = 
            "SELECT 
                rsd.day_of_week,
                rs.id AS restaurant_service_id,
                rs.service_type,
                rs.opening_time,
                rs.closing_time
            FROM app_back.restaurant_service rs
            JOIN app_back.restaurant_service_day rsd 
                ON rs.id = rsd.restaurant_service_id
            WHERE rs.restaurant_id = :restaurant_id
            ORDER BY rsd.day_of_week, rs.service_type, rs.opening_time;"
        ;
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute(['restaurant_id' => $restaurantId]);
            $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            if (empty($result)) {
                throw new NotFoundException(message:__METHOD__ . ": Aucun jour ouvré trouvé au restaurant '$restaurantId'.", UIMessage:"Service non trouvé.");
            }
            return $result;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . "Echec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
}