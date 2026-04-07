<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\RenderService;
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
    public function __construct(RestaurantService $restaurantService, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->restaurantService = $restaurantService;
    }
    
    /**
     * index charge la page de gestion des horraires et du nombre de convives du restaurant.
     *
     * @param  array $extraData
     * @return void
     */
    public function index(array $extraData = [], int $http = 200): Response
    {
        $data = [];
        $errorMessage = null;
        $http = $http === 200 ? $http : $http;
        try {
            $servicesData = $this->restaurantService->getRestaurantServices();
            $data = array_merge($servicesData, $extraData);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $errorMessage = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        if (!is_null($errorMessage)) {
            $data['errorMessage'] = $errorMessage;
        }
        $content = $this->renderService->render("admin.restaurant", $data);
        return $this->html($content, $http);
    }
    
    /**
     * updateRestaurant
     *
     * @return void
     */
    public function updateRestaurant()
    {
        $confirmationMessage = null;
        $errorMessage = null;
        $http = 200;
        try {
            unset($_POST['csrf_token']);
            $this->restaurantService->updateRestaurantServices($_POST);
            $confirmationMessage = "Modifications enregistrées avec succès!";
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $errorMessage = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        $data = [
            "confirmationMessage" => $confirmationMessage,
            "errorMessage" => $errorMessage
        ];
        return $this->index($data, $http);
    }
}