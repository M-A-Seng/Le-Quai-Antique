<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\NotFoundException;
use App\Services\RestaurantService;

/**
 * RestaurantController
 * 
 * - index()
 * - updateRestaurant()
 */
class RestaurantController extends AbstractController
{
    private RestaurantService $restaurantService;
    
    /**
     * __construct
     *
     * @param  RestaurantService $restaurantService
     * @return void
     */
    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }
    
    /**
     * index charge la page de gestion des horraires et du nombre de convives du restaurant.
     *
     * @param  array $extraData
     * @return void
     */
    public function index(array $extraData = []): void
    {
        $data = [];
        $errorMessage = null;
        try {
            $restaurant = $this->restaurantService->getRestaurant();
            $times = [
                'lunchOpeningTime' => 'lunch_opening_time',
                'lunchClosingTime' => 'lunch_closing_time',
                'eveningOpeningTime' => 'evening_opening_time',
                'eveningClosingTime' => 'evening_closing_time'
            ];
            foreach ($times as $key => $column) {
                $times[$key] = $this->restaurantService->formatTimeToHHMM($restaurant[$column]);
            }
            $maxGuests = [
                "lunchMaxGuests" => $restaurant['lunch_max_guests'],
                "eveningMaxGuests" => $restaurant['evening_max_guests']
            ];
            $data = array_merge($times, $maxGuests, $extraData);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            http_response_code($e->getHttpCode());
            $errorMessage = $e->getUIMessage();
            error_log($e->getMessage());
        }
        if (!is_null($errorMessage)) {
            $data['errorMessage'] = $errorMessage;
        }
        $this->render("admin.restaurant", $data);
    }
    
    /**
     * updateRestaurant
     *
     * @return void
     */
    public function updateRestaurant(): void
    {
        $this->requirePostMethod();
        $this->checkCsrfToken();
        $confirmationMessage = '';
        $errorMessage = '';
        try {
            unset($_POST['csrf_token']);
            $this->restaurantService->updateRestaurantServices($_POST);
            $confirmationMessage = "Modifications enregistrées avec succès!";
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            http_response_code($e->getHttpCode());
            $errorMessage = $e->getUIMessage();
            error_log($e->getMessage());
        }
        $data = [
            "confirmationMessage" => $confirmationMessage,
            "errorMessage" => $errorMessage
        ];
        $this->index($data);
    }
}