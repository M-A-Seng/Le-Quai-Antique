<?php

namespace App\Services;

use App\Core\Abstract\AbstractDataProcessingService;
use App\Exceptions\InvalidFieldException;
use App\Models\RestaurantModel;

/**
 * RestaurantService implémente les opérations de gestion des horraires et du nombre de convives du restaurant.
 * 
 * - updateRestaurantServices()
 * - getRestaurant()
 */
class RestaurantService extends AbstractDataProcessingService
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
        $adminId = $_SESSION['id'] ?? 0;
        return $this->restaurantModel->getRestaurantByAdmin((int)$adminId);
    }
    
    /**
     * getRestaurantServices retourne les heures d'ouverture et de fermeture ainsi que le nombre de convives du restaurant.
     *
     * @return array
     */
    public function getRestaurantServices(): array
    {
        $restaurant = $this->getRestaurant();
        $times = [
            'lunchOpeningTime' => 'lunch_opening_time',
            'lunchClosingTime' => 'lunch_closing_time',
            'eveningOpeningTime' => 'evening_opening_time',
            'eveningClosingTime' => 'evening_closing_time',
        ];
        foreach ($times as $key => $column) {
            $times[$key] = $this->formatTimeToHHMM((string)$restaurant[$column]);
        }
        $maxGuests = [
            "lunchMaxGuests" => 'lunch_max_guests',
            "eveningMaxGuests" => 'evening_max_guests',
        ];
        foreach ($maxGuests as $key => $column) {
            $maxGuests[$key] = $this->validatePositiveInteger((string)$restaurant[$column]);
        }
        return array_merge($times, $maxGuests);
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
        $this->trimStringValuesInArray($data);

        foreach ($data as $key => $value)
        {
            if (is_array($value)) {
                $array = print_r($value, true);
                throw new InvalidFieldException("La valeur de '$key' est invalide : " . $array . "\nUne chaîne de caractères est attendue.");
            }
            if (!in_array($key, static::NOT_NULL_COLUMNS, true)) {
                throw new InvalidFieldException("Le champ '$key' est invalide.");
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