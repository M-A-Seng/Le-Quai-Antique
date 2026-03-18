<?php

namespace App\Services;

use App\Core\AbstractDataValidationService;
use App\Core\Auth;
use App\Exceptions\ValidationException;
use App\Models\RestaurantModel;
use InvalidArgumentException;

/**
 * RestaurantService implémente les opérations de gestion des horraires et du nombre de convives du restaurant.
 * 
 * - updateRestaurantServices()
 */
class RestaurantService extends AbstractDataValidationService
{
    private RestaurantModel $restaurantModel;
    protected const NOT_NULL_COLUMNS = [
        "lunch_opening_time",
        "lunch_closing_time",
        "lunch_max_guests",
        "evening_opening_time",
        "evening_closing_time",
        "evening_max_guests"
    ];
    
    public function __construct(RestaurantModel $restaurantModel)
    {
        $this->restaurantModel = $restaurantModel;
    }
        
    /**
     * getRestaurant retourne le restaurant associé à l'administrateur connecté.
     *
     * @return void
     */
    public function getRestaurant()
    {
        return $this->restaurantModel->getRestaurantByAdmin($_SESSION['id']);
    }

    /**
     * updateRestaurantServices met à jour les horraires d'ouverture et le nombre de convives du restaurant.
     *
     * @param  array $data
     * @return void
     */
    public function updateRestaurantServices(array $data): void
    {
        $this->validateNotNullKeys(static::class, $data, false);
        $this->trimAllValuesInArray($data);

        foreach ($data as $key => $value)
        {
            if (is_array($value)) {
                throw new ValidationException("La valeur '$value' est invalide.");
            }
            if (!in_array($key, static::NOT_NULL_COLUMNS, true)) {
                throw new ValidationException("Le champ '$key' est invalide.");
            }

            $lastWord = substr($key, strrpos($key, '_') + 1);
            switch ($lastWord) {
                case 'time':
                    $this->validateTimeFormat($value);
                    break;
                case 'guests':
                    $this->validatePositiveInteger($value);
                    break;
                default:
                    break;
            }
        }
        if (isset($data['lunch_opening_time'])) {
            $this->validateTimeInterval($data['lunch_opening_time'], $data['lunch_closing_time'], 120);
        }
        if (isset($data['evening_opening_time'])) {
            $this->validateTimeInterval($data['evening_opening_time'], $data['evening_closing_time'], 120);
        }
        $this->restaurantModel->updateRestaurant($data);
    }
}