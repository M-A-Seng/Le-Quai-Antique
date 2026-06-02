<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\DayOfWeek;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\ServerException;
use App\Models\RestaurantServiceModel;
use DateTimeImmutable;

/**
 * RestaurantServiceService
 * 
 * - getRestaurantServices()
 * - getRestaurantServiceByTime()
 * - getServiceTimeSlotsByDate()
 * - updateRestaurantService()
 */
class RestaurantServiceService extends AbstractService
{
    # Constante utilisée par AbstractService
    protected const NOT_NULL_COLUMNS = [
        "id",
        "opening_time",
        "closing_time",
        "max_guests",
    ];
    private array $expectedInputs = [
        "id",
        "opening_time",
        "closing_time",
        "max_guests",
    ];
        
    /**
     * __construct
     *
     * @param  RestaurantServiceModel $restaurantServiceModel
     * @param DatetimeService $datetimeService
     * @return void
     */
    public function __construct(private RestaurantServiceModel $restaurantServiceModel,
                                private ServiceService $serviceService, 
                                private DatetimeService $datetimeService) {}
    
    /**
     * getRestaurantServices retourne les différents services avec leur nombre de convive du restaurant spécifié.
     *
     * @param  int $restaurantId
     * @return array
     */
    public function getRestaurantServices(int $restaurantId): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);
        
        if (empty($restaurantId)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez entrer l'id du restaurant en paramètre.");
        }
        $result = $this->restaurantServiceModel->findRestaurantServicesByRestaurantId($restaurantId);
        $restaurantServices = [];
        # logique adaptée pour 1 service_type/restaurant uniquement
        foreach ($result as &$data) {
            $data['opening_time'] = $this->datetimeService->formatTimeToHHMM($data['opening_time']);
            $data['closing_time'] = $this->datetimeService->formatTimeToHHMM($data['closing_time']);

            if (isset($restaurantServices[$data['service_type']])) {
                throw new ServerException(__METHOD__ . ": Duplicate service_type détecté : " . $data['service_type']);
            }
            $restaurantServices[$data['service_type']] = $data;
        }
        unset($data);
        return $restaurantServices;
    }
    
    /**
     * getRestaurantServiceByTime retourne le service dont la plage horaire inclue l'heure donnée.
     *
     * @param  int $restaurantId
     * @param  string $time
     * @return array
     */
    public function getRestaurantServiceByTime(int $restaurantId, string $time): array
    {
        $this->validatePositiveInteger($restaurantId);
        if ($this->datetimeService->validateTimeFormat($time, return:true) === false) {
            $time = $this->datetimeService->formatTimeToHHMM($time) . ":00";
        }
        return $this->restaurantServiceModel->findRestaurantServiceByTime($restaurantId, $time);
    }
    
    /**
     * getServiceTimeSlot retourne les plages horaires d'ouverture pour le jour donnée et indique si la plage est complète ou pas.
     *
     * @param  int $restaurantId
     * @param  string $date | Y-m-d
     * @return ?array
     */
    public function getServiceTimeSlotsByDate(int $restaurantId, string $date): ?array
    {
        $this->validatePositiveInteger($restaurantId);
        $this->datetimeService->validateDateYmdFormat($date);

        $day = new DateTimeImmutable($date)->format('l');
        $result = $this->restaurantServiceModel->findRestaurantServicesByOpeningDay($restaurantId, DayOfWeek::from(strtoupper($day)));
        $data = [];
        if ($result) {
            foreach ($result as $restaurantService) {
                $service = $this->serviceService->findServiceByDateTime(1, $date, $restaurantService['opening_time']);
                $capacity = $service['max_guests'] ?? $restaurantService['max_guests'];
                $remaining = $service === null 
                             ? $capacity # service non trouvé = 100% places disponibles (création service automatisée par ReservationService::addReservation())
                             : $this->serviceService->getRemainingPlacesInService($service['id']);
                $data[] = [
                    'start' => $this->datetimeService->formatTimeToHHMM($restaurantService['opening_time']),
                    'end'   => $this->datetimeService->formatTimeToHHMM($restaurantService['closing_time']),
                    'complete' => (int)$remaining <= 0
                ];
            }
        }
        return $data;
    }
    
    /**
     * updateRestaurantService met à jour 1 service.
     *
     * @param  int $serviceId
     * @param  int $restaurantId
     * @param  array $data
     * @return array
     */
    public function updateRestaurantService(int $serviceId, int $restaurantId, array $data): array
    {
        # valider paramètres
        $this->validatePositiveInteger($data['user_id'] ?? 0);
        $this->checkUserLegitimacy($data['user_id'], [Role::ADMIN]);

        $this->validatePositiveInteger($serviceId);
        $this->validatePositiveInteger($restaurantId);
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau assotiatif attendu en troisième paramètre.");
        }
        $this->checkExpectedKeys($this->expectedInputs, $data);
        $this->trimStringValuesInArray($data);

        # valider data
        $this->validateNotNullKeys(static::class, $data, true);
        $this->datetimeService->validateTimeFormat($data['opening_time'], false);
        $this->datetimeService->validateTimeFormat($data['closing_time'], false);
        $this->validatePositiveInteger($data['max_guests']);

        # valider service
        $service = $this->restaurantServiceModel->findRestaurantServiceByServiceId($serviceId);
        if ((int)$service["restaurant_id"] !== $restaurantId) {
            throw new ServerException(__METHOD__ . ": Le service '$serviceId' n'existe pas dans le restaurant '$restaurantId'.");
        }
        if ((int)$data["id"] !== $serviceId) {
            throw new ServerException(__METHOD__ . ": Le service '$serviceId' ne correspond pas au service envoyé par le formulaire: " . $data["id"] . ".");
        }
        $serviceDuration = $this->datetimeService->toMinutes($service['service_duration']);
        $this->datetimeService->validateTimeInterval($data['opening_time'], $data['closing_time'], $serviceDuration);

        unset($data['id'], $date['user_id']);
        return $this->restaurantServiceModel->updateRestaurantService($serviceId, $data);
    }

    # La création/suppression de services de restauration n'est pas demandée dans le cahier des charges.
    // public function createRestaurantService(array $data): array {}
    // public function deleteRestaurantService(int $serviceId, int $restaurantId): void {}
}

