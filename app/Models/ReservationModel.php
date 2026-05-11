<?php

namespace App\Models;

use App\Core\Abstract\AbstractModel;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use DateTimeImmutable;
use PDO;
use PDOException;

/**
 * ReservationModel
 * 
 * - createReservation()
 * - findReservationsByUserId()
 * - findConfirmedReservationsByUserId()
 * - findReservationById()
 * - findReservationsByDate()
 * - updateReservation()
 */
class ReservationModel extends AbstractModel
{
    # Les constantes sont utilisées dans AbstractModel.
    protected const TABLE = "reservation";
    protected const ALLOWED_COLUMNS = [
        "id",
        "service_id",
        "client_id",
        "reservation_at",
        "status",
        "guest_count",
        "client_name",
        "client_tel",
        "allergy",
        "created_at",
        "updated_at",
    ];

    private $readOnlyColumns = [
        "id",
        "created_at",
        "updated_at"
    ];
    
    /**
     * createReservation ajoute une nouvelle réservation à la table reservation.
     *
     * @param  array $data
     * @return array
     */
    public function createReservation(array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        return $this->insert($data);
    }
    
    /**
     * findAllReservations retourne toutes les réservations d'un utilisateur spécifié.
     * 
     * Exception si non trouvé.
     *
     * @param  int $userId
     * @return ?array
     */
    public function findReservationsByUserId(int $userId): ?array
    {
        $result = $this->findBy(['client_id' => $userId], ['status' => 'ASC', 'reservation_at' => 'ASC']);
        return empty($result) ? null : $result;
    }
    
    /**
     * findConfirmedReservationsByUserId retourne uniqument les réservations à venir.
     *
     * @param  int $id
     * @return ?array
     */
    public function findConfirmedReservationsByUserId(int $id): ?array
    {
        $result = $this->findBy(['client_id' => $id, 'status' => 'CONFIRMED'], ['reservation_at' => 'ASC']);
        return empty($result) ? null : $result;
    }
    
    /**
     * findReservation retourne les données d'une réservation spécifiée.
     * 
     * Exception si non trouvé.
     *
     * @param  int $reservationId
     * @return array
     */
    public function findReservationById(int $reservationId): array
    {
        $result = $this->findBy(['id' => $reservationId]);
        if (empty($result)) {
            throw new NotFoundException(message:__METHOD__ . ": Réservation '$reservationId' non trouvée en db.", UIMessage:"Réservation non trouvée.");
        }
        return $result[0];
    }
        
    /**
     * findReservationsByDate retourne toutes les réservations programmées pour une date, triées par période de la journée (midi, soir, etc.) et par ordre d'heure.
     *
     * Exception si non trouvée.
     * 
     * @param int $restaurantId
     * @param  DateTimeImmutable $datetimeTz | Date avec timezone
     * @return array
     */
    public function findReservationsByDate(int $restaurantId, DateTimeImmutable $datetimeTz): array
    {
        $sql = "SELECT 
                    s.time_of_day,
                    r.id,
                    r.service_id,
                    r.client_id,
                    r.reservation_date,
                    r.reservation_time,
                    r.status,
                    r.guest_count,
                    r.client_name,
                    r.client_tel,
                    r.allergy,
                    r.created_at,
                    r.updated_at
                FROM app_front.reservation r
                JOIN app_back.service s ON s.id = r.service_id
                WHERE 
                    s.restaurant_id = :restaurant_id
                    AND r.reservation_at::date = :reservation_date
                ORDER BY 
                    s.service_type, r.reservation_at::time, r.status;";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                'restaurant_id' => $restaurantId,
                'reservation_date' => $datetimeTz->format('Y-m-d'),
            ]);
            $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
            if (empty($result)) {
                throw new NotFoundException(message:__METHOD__ . ": Aucune réservation trouvée pour la date" . $datetimeTz->format('Y-m-d') . "dans le restaurant '$restaurantId'.", UIMessage:"Aucune réservation pour le " . $datetimeTz->format('d/m/Y'));
            }
            return $result;
        } 
        catch (PDOException $e) {
            throw new DbFailureException(__METHOD__ . ": Échec de l'opération: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * updateReservation modifie une réservation existante.
     *
     * @param  int $reservationId
     * @param  array $data
     * @return array
     */
    public function updateReservation(int $reservationId, array $data): array
    {
        $this->checkProtectedColumns($data, $this->readOnlyColumns);
        $this->findReservationById($reservationId);

        return $this->update($reservationId, $data);
    }
}