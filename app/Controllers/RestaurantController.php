<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Services\RestaurantService;
use InvalidArgumentException;
use RuntimeException;

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
     * @return void
     */
    public function index(array $extraData = []): void
    {
        $restaurant = $this->restaurantService->getRestaurant();
        $viewData = [
            "lunchOpeningTime" => substr($restaurant['lunch_opening_time'], 0, 5),
            "lunchClosingTime" => substr($restaurant['lunch_closing_time'], 0, 5),
            "lunchMaxGuests" => $restaurant['lunch_max_guests'],
            "eveningOpeningTime" => substr($restaurant['evening_opening_time'], 0, 5),
            "eveningClosingTime" => substr($restaurant['evening_closing_time'], 0, 5),
            "eveningMaxGuests" => $restaurant['evening_max_guests']
        ];
        $data = array_merge($viewData, $extraData);
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
        catch (ValidationException | NotFoundException | RuntimeException $e) {
            $errorMessage = "Une erreur est survenue, veuillez réessayer ou revenez plus tard.";
        }
        catch (InvalidArrayForDbException $e) {
            $errorMessage = "Veuillez remplir les champs demandés.";
        }
        catch (InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
        }
        $data = [
            "confirmationMessage" => $confirmationMessage,
            "errorMessage" => $errorMessage
        ];
        $this->index($data);
    }
}