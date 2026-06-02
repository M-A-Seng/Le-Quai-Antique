<?php

namespace App\Services;

use App\Core\Abstract\AbstractService;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\DbFailureException;
use App\Models\RestaurantServiceModel;
use App\Models\ServiceModel;
use DateTimeImmutable;

/**
 * ServiceService
 * 
 * - getServiceById()
 * - findServiceByDateTime()
 * - newService()
 * - modifyService()
 * - getRemainingPlacesInService()
 */
class ServiceService extends AbstractService
{
    # Constante utilisée par AbstractService
    protected const NOT_NULL_COLUMNS = [
        "restaurant_id",
        "open_at",
        "close_at",
        "service_type",
        "max_guests",
    ];

    public function __construct(private ServiceModel $serviceModel, 
                                private RestaurantServiceModel $restaurantServiceModel,
                                private DatetimeService $datetimeService) {}
        
    /**
     * getServiceById retourne le service demandé
     *
     * @param  int $id
     * @return array
     */
    public function getServiceById(int $id): array
    {
        $this->validatePositiveInteger($id);
        return $this->serviceModel->findServiceById($id);
    }

    /**
     * findServiceByDateTime retourne le service correspondant à la date et à l'heure spécifiée.
     *
     * @param  string $date | YYYY-MM-DD
     * @param  string $time | HH:MM(:SS)
     * @return ?array
     * 
     * @throws DataProcessingException
     */
    public function findServiceByDateTime(int $restaurantId, string $date, string $time): ?array
    {
        $this->validatePositiveInteger($restaurantId);
        if (empty($date)) {
            throw new DataProcessingException(__METHOD__ . ": Date attendue en deuxième paramètre");
        }
        $this->datetimeService->validateDateYmdFormat($date);
        $time = !empty($time) 
                ? $this->datetimeService->formatTimeToHHMM($time, strict:true) 
                : throw new DataProcessingException(__METHOD__ . ": Heure attendue en troisième paramètre");
        $this->datetimeService->validateTimeFormat($time, strict:true);

        $datetimeTz = $this->datetimeService->formatDateTimeToDatetimeTzOrISO($date, $time);
        return $this->serviceModel->findServiceByTimestamptz($restaurantId, $datetimeTz);
    }
    
    /**
     * newService Créer un nouveau service pour la date donnée si inexistant.
     *
     * @param  int $restaurantId
     * @param  DateTimeImmutable $reservationDatetimeFrTz
     * @return array
     */
    public function newService(int $restaurantId, DateTimeImmutable $reservationDatetimeFrTz): array
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN, Role::CLIENT]);
        $this->validatePositiveInteger($restaurantId);
        $reservationTimestamptz = $reservationDatetimeFrTz->format('Y-m-d H:i:sP');

        # service existe déjà?
        $service = $this->serviceModel->findServiceByTimestamptz($restaurantId, $reservationTimestamptz);
        if (!empty($service)) {
            return $service;
        }

        $formattedTime = $reservationDatetimeFrTz->format('H:i');
        $restaurantService = $this->restaurantServiceModel->findRestaurantServiceByTime($restaurantId, $formattedTime);
        # nouveau service
        list($oph, $opm, $ops) = explode(":", $restaurantService['opening_time']);
        list($clh, $clm, $cls) = explode(":", $restaurantService['closing_time']);
        $openAt = $reservationDatetimeFrTz->setTime($oph, $opm, $ops);
        $closeAt = $reservationDatetimeFrTz->setTime($clh, $clm, $cls);

        $data = [
            'restaurant_id' => $restaurantId,
            'open_at' => $openAt->format('Y-m-d H:i:sP'),
            'close_at' => $closeAt->format('Y-m-d H:i:sP'),
            'service_type' => $restaurantService['service_type'],
            'max_guests' => $restaurantService['max_guests'],
        ];
        $this->validateNotNullKeys(static::class, $data, true);
        try {
            return $this->serviceModel->createService($data);
        } 
        catch (DbFailureException $e) {
            if ($e->getCode() === '23505') {
                # 23505 code de violation d'unicité POSTGRESQL (signifie que le servie a déjà été créé entre temps)
                return $this->serviceModel->findServiceByTimestamptz($restaurantId, $reservationTimestamptz);
            } else {
                throw $e;
            }
        }
    }
    
    /**
     * modifyService
     *
     * @param  int $serviceId
     * @param  array $data
     * @return void
     */
    public function modifyService(int $serviceId, array $data): void
    {
        $this->checkUserLegitimacy(roles:[Role::ADMIN]);

        $this->validatePositiveInteger($serviceId);
        if (empty($data) || array_is_list($data)) {
            throw new DataProcessingException(__METHOD__ . ": Tableau associatif attendu en deuxième paramètre.");
        }
        $data = $this->trimStringValuesInArray($data);
        $this->validateNotNullKeys(static::class, $data, false);
        $this->serviceModel->findServiceById($serviceId); # service existe?

        $this->serviceModel->updateService($serviceId, $data);
    }
    
    /**
     * getRemainingPlacesInService retourne le nombre de places restantes dans un service.
     *
     * @param  int $serviceId
     * @return int
     */
    public function getRemainingPlacesInService(int $serviceId): int
    {
        $this->validatePositiveInteger($serviceId);
        return $this->serviceModel->calculateRemainingPlacesInService($serviceId);
    }
}