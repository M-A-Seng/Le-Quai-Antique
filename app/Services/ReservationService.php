<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidFieldException;
use App\Exceptions\InvalidReservationException;
use App\Exceptions\ServerException;
use App\Models\ReservationModel;
use App\Models\RestaurantServiceModel;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * ReservationService
 * 
 * - addReservation()
 * - getReservationById()
 * - getUserReservations()
 * - getReservationsByDate()
 * - modifyReservation()
 * - changeReservationStatus()
 * - validateReservationData()
 * - isValidReservationDateTime()
 * - hasCapacityForReservation()
 * - getFrenchFormatedDate()
 */
class ReservationService extends AbstractService
{
    # Constante utilisée par AbstractService
    protected const NOT_NULL_COLUMNS = [
        "service_id",
        "client_id",
        "reservation_at",
        "status",
        "guest_count",
        "client_name",
    ];
    private array $reservationStatus = [
        'CONFIRMED' => 'CONFIRMÉ',
        'COMPLETED' => 'TERMINÉ',
        'CANCELED' => 'ANNULÉ'
    ];
    private array $reservationExpectedInput = [
        'id',
        'user_id',
        'service_id',
        'reservation_date',
        'reservation_time',
        'guest_count',
        'client_id',
        'client_name',
        'client_tel',
        'allergy',
        'action'
    ];
    private array $serviceType = [
        'BREAKFAST' => "Petit Déjeuner",
        'BRUNCH' => "Brunch",
        'LUNCH' => "Déjeuner",
        'SNACK' => "Goûter",
        'DINNER' => "Dîner"
    ];
    private DateTimeZone $timezone;

    public function __construct(private ReservationModel $reservationModel, 
                                private DatetimeService $datetimeService,
                                private ServiceService $serviceService, 
                                private RestaurantServiceModel $restaurantServiceModel,
                                private OpeningDayService $openingDayService) 
    {
        $this->timezone = $this->datetimeService->getLocalTimezone();
    }
    
    /**
     * addReservation ajoute une nouvelle réservation
     *
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     * 
     * @throws DataProcessingException
     */
    public function addReservation(int $restaurantId, array $data): array
    {
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        $this->validatePositiveInteger($data['client_id'] ?? 0);
        $this->checkUserLegitimacy($data['client_id'], [Role::ADMIN, Role::CLIENT]);
        $this->validatePositiveInteger($restaurantId);

        # valider données
        $verified = $this->validateReservationData($data);
        $reservationDatetimeFrTz = $this->isValidReservationDateTime($restaurantId, $verified['date'], $verified['time']); # Valider jour ouvré
        
        # Créer service si inexistant
        $service = $this->serviceService->findServiceByDateTime($restaurantId, $verified['date'], $verified['time'])
                   ?? $this->serviceService->newService($restaurantId, $reservationDatetimeFrTz);

        $data = [
            'service_id' => $service['id'],
            'client_id' => $verified['client_id'],
            'reservation_at' => $reservationDatetimeFrTz->format('Y-m-d H:i:sP'),
            'status' => ReservationStatus::CONFIRMED->value,
            'guest_count' => $verified['guest_count'],
            'client_name' => $verified['client_name'],
            'client_tel' =>  $verified['client_tel'],
            'allergy' => $verified['allergy'],
        ];
        $this->validateNotNullKeys(static::class, $data, true);
        return $this->reservationModel->createReservation($data);
    }
     
    /**
     * getReservationById retourne les données d'une réservation.
     *
     * @param  int $id
     * @param  bool $formattedForForm | true: pour formulaire / false: pour affichage
     * @return array
     */
    public function getReservationById(int $id, bool $formattedForForm = false): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN, Role::CLIENT]);
        $this->validatePositiveInteger($id);
        $result = $this->reservationModel->findReservationById($id);
        if (empty($result)) {
            return [];
        }
        $reservationDate = $this->datetimeService->formatDatetimeTzOrISOToLocal($result['reservation_at'], true);
        if (!empty($result['created_at']) && !empty($result['updated_at'])) {
            $created = $this->datetimeService->formatDatetimeTzOrISOToLocal($result['created_at'], true);
            $updated = $this->datetimeService->formatDatetimeTzOrISOToLocal($result['updated_at'], true);
        }
        $base = [
            'id' => $result['id'],
            'service_id' => $result['service_id'],
            'status' => $this->reservationStatus[$result['status']] ?? null,
            'guest_count' => $result['guest_count'],
            'client_name' => $result['client_name'],
            'client_tel' => $result['client_tel'] ?? null,
            'created_at' => $created['datetime'] ?? null,
            'updated_at' => $updated['datetime'] ?? null,
        ];
        $forDisplay = [
            'date_fullformat' => ($_SESSION['role']->value === 'ADMIN' ? $reservationDate['datetime'] : $reservationDate['full_french_format']) ?? null,
            'reservation_date' => ($_SESSION['role']->value === 'ADMIN' ? $reservationDate['date'] : $reservationDate['french_format']) ?? null,
            'reservation_time' => $reservationDate['time'] ?? null,
            'allergy' => $result['allergy'],
        ];
        $forForm = [
            'reservation_date' => $reservationDate['Y-m-d'] ?? null,
            'default_reservation_time' => $reservationDate['time'] ?? null,
            'allergy' => !empty($result['allergy']) ? explode(', ', $result['allergy']) : null,
        ];
        return $formattedForForm ? array_merge($base, $forForm) : array_merge($base, $forDisplay);
    }
      
    /**
     * getUserReservations
     *
     * @param  int $userId
     * @param  bool $confirmedOnly
     * @return ?array
     */
    public function getUserReservations(int $userId, bool $confirmedOnly = false) : ?array
    {
        $this->validatePositiveInteger($userId);
        $this->checkUserLegitimacy($userId, [Role::CLIENT]);
        $data = [];
        $result = $confirmedOnly ? 
                  $this->reservationModel->findConfirmedReservationsByUserId($userId)
                  : $this->reservationModel->findReservationsByUserId($userId);
        
        if ($result === null) { return null; }

        foreach ($result as $row) {
            $reservationDate = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['reservation_at'], true);
            $created = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['created_at'], true);
            $updated = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['updated_at'], true);
            $data[] = [
                'id' => $row['id'],
                'service_id' => $row['service_id'],
                'date_fullformat' => ($_SESSION['role']->value === 'ADMIN' ? $reservationDate['datetime'] : $reservationDate['full_french_format']) ?? '',
                'reservation_date' => ($_SESSION['role']->value === 'ADMIN' ? $reservationDate['date'] : $reservationDate['french_format']) ?? '',
                'reservation_time' => $reservationDate['time'] ?? '',
                'status' => $this->reservationStatus[$row['status']] ?? '',
                'client_name' => $row['client_name'],
                'client_tel' => $row['client_tel'] ?? '',
                'guest_count' => $row['guest_count'],
                'allergy' => $row['allergy'] ?? '',
                'created_at' => $created['datetime'] ?? '',
                'updated_at' => $updated['datetime'] ?? '',
            ];
        }
        return $data;
    }
    
    /**
     * getReservationsByDate retourne toutes les données pour la page admin.reservations.php.
     *
     * @param  int $restaurantId
     * @param  string $date | YYYY-mm-dd
     * @return ?array
     * 
     * @throws InvalidFieldException
     */
    public function getReservationsByDate(int $restaurantId, string $date) : ?array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
    
        $this->validatePositiveInteger($restaurantId);
        $this->datetimeService->validateDateYmdFormat($date);
        try {
            $datetime = new DateTimeImmutable($date, $this->timezone);
        } catch (Exception $e) {
            throw new InvalidFieldException("Sélectionnez une date valide pour afficher les réservations.");
        }
        $result = $this->reservationModel->findReservationsByDate($restaurantId, $datetime);
        if ($result === null) {return null;} // early return

        $data = [];
        $first = true;
        $reservation = [];
        $params = [];
        $max_guests = [];
        $remaining = [];

        foreach ($result as $serviceTypeEn => $group) 
        {
            $params['service_types'][strtolower($serviceTypeEn)] = $this->serviceType[$serviceTypeEn] ?? $serviceTypeEn;

            foreach ($group as $row) {
                $reservationDate = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['reservation_at'], true);
                $created = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['created_at'], true);
                $updated = $this->datetimeService->formatDatetimeTzOrISOToLocal($row['updated_at'], true);

                if ($first) {
                    $params["french_formated_date"] = $reservationDate['french_format'];
                    $params['display_by_default'] = strtolower($serviceTypeEn);
                    $first = false;
                }
                $reservation[strtolower($serviceTypeEn)][] = [
                    "id" => $row['id'],
                    "service_id" => $row['service_id'],
                    "client_id" => $row['client_id'],
                    "date_fullformat" => $reservationDate['full_french_format'],
                    "reservation_date" => $reservationDate['date'],
                    "reservation_time" => $reservationDate['time'],
                    "status" => $this->reservationStatus[$row['status']],
                    "guest_count" => $row['guest_count'],
                    "client_name" => $row['client_name'],
                    "client_tel" => $row['client_tel'] ?? '',
                    "allergy" => $row['allergy'] ?? '',
                    "created_at" => $created['datetime'],
                    "updated_at" => $updated['datetime'],
                ];
                // condition if pour éviter de boucler 2 fois le même service
                if (!isset($max_guests[strtolower($serviceTypeEn)])) {
                    $max_guests[strtolower($serviceTypeEn)] = $this->serviceService->getServiceById($row['service_id'])['max_guests'];
                }
                if (!isset($remaining[strtolower($serviceTypeEn)])) {
                    $remaining[strtolower($serviceTypeEn)] = $this->serviceService->getRemainingPlacesInService($row['service_id']);
                }
            }
        }
        unset($serviceType, $row);
        $data = [
            'service_types' => $params['service_types'],
            'french_formated_date' => $params['french_formated_date'],
            'display_by_default' => $params['display_by_default'],
            'reservations' => $reservation,
            'day_before' => $datetime->modify('-1 day')->format('d-m-Y'),
            'day_after' => $datetime->modify('+1 day')->format('d-m-Y'),
            'max_guests' => $max_guests,
            'remaining_places' => $remaining
        ];
        return $data;
    }
    
    /**
     * modifyReservation Modifie une réservation.
     *
     * @param  int $reservationId
     * @param  array $data
     * @return array
     * 
     * @throws DataProcessingException
     * @throws ServerException
     */
    public function modifyReservation(int $reservationId, array $data): array
    {
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        $this->validatePositiveInteger($data['user_id']);
        $this->checkUserLegitimacy($data['user_id'], [Role::ADMIN, Role::CLIENT]);
        $this->validatePositiveInteger($reservationId);

        # valider données
        $reservation = $this->reservationModel->findReservationById($reservationId); # reservation existe?
        $verified = $this->validateReservationData($data, false);
        # valider date/heure
        try {
            $currentReservation = new DateTimeImmutable($reservation['reservation_at'])->setTimezone($this->timezone);
        } catch (Exception $e) {
            throw new ServerException(__METHOD__ . ": Date/heure invalide stockée en db: " . $e->getMessage(), 0, $e);
        }
        $serviceId = $reservation['service_id'];
        $currentService = $this->serviceService->getServiceById($serviceId);
        $restaurantId = $currentService['restaurant_id'];
        $modifiedReservation = $this->isValidReservationDateTime($restaurantId, $verified['date'], $verified['time']); # Valider jour ouvré
        
        # A changé la date / heure ?
        if ($currentReservation->getTimestamp() !== $modifiedReservation->getTimestamp()) {
            # Créer service si inexistant
            $service = $this->serviceService->findServiceByDateTime($restaurantId, $verified['date'], $verified['time'])
                          ?? $this->serviceService->newService($restaurantId, $modifiedReservation);
            $serviceId = $service['id'];
        }
        $data = [
            'service_id' => $serviceId,
            'client_id' => $verified['client_id'] ?? $reservation['client_id'],
            'reservation_at' => $modifiedReservation->format('Y-m-d H:i:sP'),
            'guest_count' => $verified['guest_count'],
            'client_name' => $verified['client_name'],
            'client_tel' =>  $verified['client_tel'],
            'allergy' => $verified['allergy'],
        ];
        $this->validateNotNullKeys(static::class, $data, false);
        return $this->reservationModel->updateReservation($reservationId, $data);
    }
    
    /**
     * changeReservationStatus change le status d'une réservation
     *
     * @param  int $reservationId
     * @param  ReservationStatus $reservationStatus
     * @return void
     */
    public function changeReservationStatus(int $userId, int $reservationId, ReservationStatus $reservationStatus): void
    {
        $this->validatePositiveInteger($userId);
        $this->checkUserLegitimacy($userId, [Role::ADMIN, Role::CLIENT]);
    
        $this->validatePositiveInteger($reservationId);
        $reservation = $this->reservationModel->findReservationById($reservationId); # reservation existe?
        
        if ((string)$reservation['status'] !== (string)$reservationStatus->value) {
            $this->reservationModel->updateReservation($reservationId, ['status' => $reservationStatus->value]);
        }
    }
    
    /**
     * validateReservationData vérifie les données passées en entrée par l'utilisateur et les retourne prêt à l'emploi.
     *
     * @param  array $data
     * @param bool $clientId | true si l'utilisateur est authentifié
     * @return array
     * 
     * @throws InvalidFieldException
     * @throws DataProcessingException
     * @throws InvalidFieldException
     */
    public function validateReservationData(array $data, bool $clientId = true): array
    {
        $this->checkExpectedKeys($this->reservationExpectedInput, $data);
        $data = $this->trimStringValuesInArray($data);

        $client = !empty($data['client_name']) ? $data['client_name'] : throw new InvalidFieldException("Veuillez indiquer à quel nom vous réservez.");
        $date = !empty($data['reservation_date']) ? $data['reservation_date'] : throw new InvalidFieldException("Veuillez fournir une date de réservation.");
        $this->datetimeService->validateDateYmdFormat($date);
        $time = !empty($data['reservation_time']) ? $data['reservation_time'] : throw new InvalidFieldException("Veuillez fournir une heure de réservation.");
        $time = $this->datetimeService->formatTimeToHHMM($time, strict:true);
        $this->datetimeService->validateTimeFormat($time, strict:true);

        if ($clientId) {
            if (empty($data['client_id']) || $this->validatePositiveInteger($data['client_id'], return:true) === false) {
                throw new DataProcessingException(__METHOD__ . ": client_id non fourni dans data.");
            }
        }
        if (empty($data['guest_count']) || $this->validatePositiveInteger($data['guest_count'], return:true) === false) {
            throw new InvalidFieldException("Veuillez indiquer pour combien de personnes vous réservez.");
        }
        $allergy = null;
        if (!empty($data['allergy'])) {
            if (is_array($data['allergy'])) {
                $allergy = implode(', ', $data['allergy']);
            }
            elseif (is_string($data['allergy'])) {
                $allergy = $data['allergy'];
            }
            else { $allergy = null; }
        }
        $clientTel = null;
        if (!empty($data['client_tel'])) {
            $clientTel = $this->phoneNumberCheckAndSanitize($data['client_tel']);
        }
        return [
            'client_id' => $clientId ? $data['client_id'] : null,
            'date' => $date,
            'time' => $time,
            'client_name' => $client,
            'guest_count' => $data['guest_count'],
            'allergy' => $allergy,
            'client_tel' => $clientTel,
        ];
    }
    
    /**
     * isValidReservationDateTime vérifie que la date/heure est conforme aux horaires d'ouvertures du restaurant. Retourne la réservation en objet DateTimeImmutable.
     *
     * @param  int $restaurantId
     * @param  string $date
     * @param  string $time
     * @return DateTimeImmutable
     * 
     * @throws InvalidReservationException
     * @throws InvalidFieldException
     */
    public function isValidReservationDateTime(int $restaurantId, string $date, string $time): DateTimeImmutable
    {
        $this->datetimeService->validateDateYmdFormat($date);
        $this->datetimeService->validateTimeFormat($time);

        $reservationDate = new DateTimeImmutable("$date $time", $this->timezone);
        $today = new DateTimeImmutable(timezone:$this->timezone);
        if ($reservationDate->getTimestamp() <= $today->getTimestamp()) {
            throw new InvalidReservationException("Veuillez sélectionner une date et heure valide.");
        }
        $formattedTime = $reservationDate->format('H:i');
        $restaurantService = $this->restaurantServiceModel->findRestaurantServiceByTime($restaurantId, $formattedTime . ":00");

        if (!$restaurantService) {
            throw new InvalidReservationException("Le restaurant est fermé à $formattedTime. Veuillez sélectionner une heure comprise dans les horaires d'ouverture.");
        }
        if ($this->openingDayService->isServiceOpenThatDay($restaurantService['id'], $reservationDate->format('Y-m-d')) === false) {
            $datetimeTz = $this->datetimeService->formatDateTimeToDatetimeTzOrISO($date, $time, true, false);
            $local = $this->datetimeService->formatDatetimeTzOrISOToLocal($datetimeTz);
            throw new InvalidReservationException("Le restaurant est fermé le " . $local['full_french_format'] . ". Veuillez sélectionner une date et heure comprise dans les horaires d'ouverture.");
        }
        try {
            $reservationDatetime = new DateTimeImmutable($date." ".$time, $this->timezone);
        } catch (Exception $e) {
            throw new InvalidFieldException("Date ou heure de réservation invalide.");
        }
        return $reservationDatetime;
    } 
    
    /**
     * hasCapacityForReservation retourne true si places disponibles.
     *
     * @param  string $reservationDate
     * @param  string $reservationTime
     * @param  int $guestNumber
     * @return bool
     */
    public function hasCapacityForReservation(string $reservationDate, string $reservationTime, int $guestNumber): bool
    {
        $this->validatePositiveInteger($guestNumber);
        $this->datetimeService->validateDateYmdFormat($reservationDate);
        $reservationTime = $this->datetimeService->formatTimeToHHMM($reservationTime, true);
        $service = $this->serviceService->findServiceByDateTime(1, $reservationDate, $reservationTime);
        if ($service) {
            $remaining = $this->serviceService->getRemainingPlacesInService($service['id']);
            if ((int)$remaining >= $guestNumber) {
                return true;
            }
            return false;
        }
        return true; # service non trouvé = 100% places disponibles (création du service automatisée par addReservation())
    }
    
    /**
     * getFrenchFormatedDate retourne des dates et heures formatées sur la timezone française
     *
     * @param  string $date
     * @param  string $time
     * @return array
     * 
     * __Tableau retourné__: 
     * - 'universal' => Y-m-d H:i:sP,
     * - 'ISO' => ATOM/ISO 8601,
     * - 'Y-m-d' => Y-m-d,
     * - 'H:i:s' => H:i:s,
     * - 'datetime' => d/m/Y H:i, 
     * - 'date' => d/m/Y, 
     * - 'time' => H:i, 
     * - 'french_format' => [jour] [nn] [mois] [année]
     * - 'full_french_format' => [jour] [nn] [mois] [année] à [heure]
     */
    public function getFrenchFormatedDate(string $date, string $time): array
    {
        $this->datetimeService->validateDateYmdFormat($date);
        $this->datetimeService->validateTimeFormat($time);

        $datetimeTz = $this->datetimeService->formatDateTimeToDatetimeTzOrISO($date, $time, true, false);
        return $this->datetimeService->formatDatetimeTzOrISOToLocal($datetimeTz);
    }
}