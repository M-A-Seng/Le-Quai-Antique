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
use App\Services\RestaurantServiceService;

/**
 * RestaurantServiceController
 * 
 * - index()
 * - updateRestaurantService()
 * - getTimeSlots()
 */
class RestaurantServiceController extends AbstractController
{
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
     * @return Response
     */
    public function index(array $extraData = [], int $http = 200): Response
    {
        $data = null;
        $error_message = null;
        $servicesData = null;
        try {
            $servicesData = $this->restaurantServiceService->getRestaurantServices(1); # restaurant uniquement actuellement
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
        $page = [
            'page' => 'services',
            'error_message' => $error_message
        ];
        $data = array_merge($page, $servicesData, $extraData);
        $content = $this->renderService->render("admin.services", $data, 'user');
        return $this->html($content, $http);
    }
    
    /**
     * updateRestaurant
     *
     * @return Response
     */
    public function update(): Response
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

    /**
     * getTimeSlots AJAX avec formulaire de réservation pour générer les heures dans select. 
     *
     * @return Response
     */
    public function getTimeSlots(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            return $this->json([
                'error_message' => 'Requête invalide'
            ], 400);
        }
        $http = 200;
        $date = $data['date'] ?? '';
        try {
            $services = $this->restaurantServiceService->getServiceTimeSlotsByDate(1, $date);
            $data['services'] = !empty($services) ? $services : null;
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $data['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $data['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->json($data, $http);
    }
}