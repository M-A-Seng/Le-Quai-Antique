<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Exceptions\DataProcessingException;
use App\Exceptions\ServerException;
use App\Models\RestaurantServiceModel;

/**
 * RestaurantServiceService
 * 
 * - getRestaurantServices()
 * - updateRestaurantService()
 */
class RestaurantServiceService extends AbstractService
{
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
                                private DatetimeService $datetimeService) {}
    
    /**
     * getRestaurantServices retourne les différents services avec leur nombre de convive du restaurant spécifié.
     *
     * @param  int $restaurantId
     * @return array
     */
    public function getRestaurantServices(int $restaurantId): array
    {
        if (empty($restaurantId)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez entrer l'id du restaurant en paramètre.");
        }
        $result = $this->restaurantServiceModel->getRestaurantServicesByRestaurantId($restaurantId);
        $result = array_map(fn($rows) => $rows[0], $result); # Retirer une couche de array inutile

        foreach ($result as &$data) {
            $data['opening_time'] = $this->datetimeService->formatTimeToHHMM($data['opening_time']);
            $data['closing_time'] = $this->datetimeService->formatTimeToHHMM($data['closing_time']);
        }
        unset($data);
        return $result;
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
        $this->trimStringValuesInArray($data);
        # valider paramètres
        if (empty($serviceId) || empty($restaurantId) || empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Veuillez fournir l'id du service, du restaurant, et un tableau associatif en paramètre.");
        }
        $this->checkExpectedKeys($this->expectedInputs, $data);
        $this->validatePositiveInteger($serviceId);
        $this->validatePositiveInteger($restaurantId);

        # valider data
        $this->validateNotNullKeys(static::class, $data, true);
        $this->datetimeService->validateTimeFormat($data['opening_time'], false);
        $this->datetimeService->validateTimeFormat($data['closing_time'], false);
        $this->validatePositiveInteger($data['max_guests']);

        # valider service
        $service = $this->restaurantServiceModel->getRestaurantServiceByServiceId($serviceId);
        if ((int)$service["restaurant_id"] !== $restaurantId) {
            throw new ServerException(__METHOD__ . ": Le service '$serviceId' n'existe pas dans le restaurant '$restaurantId'.");
        }
        if ((int)$data["id"] !== $serviceId) {
            throw new ServerException(__METHOD__ . ": Le service '$serviceId' ne correspond pas au service envoyé par le formulaire: " . $data["id"] . ".");
        }
        $serviceDuration = $this->datetimeService->toMinutes($service['service_duration']);
        $this->datetimeService->validateTimeInterval($data['opening_time'], $data['closing_time'], $serviceDuration);

        unset($data['id']);
        return $this->restaurantServiceModel->updateRestaurantService((int)$service['id'], $data);
    }

    # La création/suppression de services de restauration n'est pas demandée dans le cahier des charges.
    // public function createRestaurantService(array $data): array {}
    // public function deleteRestaurantService(int $serviceId, int $restaurantId): void {}
}

