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
use App\Services\RestaurantServiceService;

/**
 * RestaurantServiceController
 * 
 * - index()
 * - updateRestaurantService()
 */
class RestaurantServiceController extends AbstractController
{
    /**
     * __construct
     *
     * @param  RestaurantService $restaurantService
     * @return void
     */
    public function __construct(private RestaurantServiceService $restaurantServiceService, 
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
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
        $error_message = null;
        try {
            $servicesData = $this->restaurantServiceService->getRestaurantServices(1);
            $data = array_merge($servicesData, $extraData);
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $error_message = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $error_message = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        if (!is_null($error_message)) {
            $data['error_message'] = $error_message;
        }
        $content = $this->renderService->render("admin.services", $data);
        return $this->html($content, $http);
    }
    
    /**
     * updateRestaurant
     *
     * @return void
     */
    public function updateRestaurantService()
    {
        $confirmation_message = null;
        $error_message = null;
        $http = 200;
        try {
            unset($_POST['csrf_token']);
            $this->restaurantServiceService->updateRestaurantService($_POST['id'], 1, $_POST);
            $confirmation_message = "Modifications enregistrées avec succès!";
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $error_message = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $error_message = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        $data = [
            "confirmation_message" => $confirmation_message,
            "error_message" => $error_message
        ];
        return $this->index($data, $http);
    }
}