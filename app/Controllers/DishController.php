<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Services\RenderService;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Services\DishService;

/**
 * DishController
 * 
 * - create()
 * - update()
 * - delete()
 * - updateOrder()
 */
class DishController extends AbstractController
{
    public function __construct(private DishService $dishService, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * create créer plat
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function create(array $param): Response
    {
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            $this->dishService->newDish(1, $_POST);
            $confirmation_message = "'".$_POST['title']."' ajouté avec succès!";
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
        $_SESSION['AdminMenuController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? null
        ];
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/plats");
    }
    
    /**
     * update modifier plat
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function update(array $param): Response
    {
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            $this->dishService->modifyDish($_POST);
            $confirmation_message = "'".$_POST['title']."' modifié avec succès!";
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
        $_SESSION['AdminMenuController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? null
        ];
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/plats");
    }
    
    /**
     * delete supprimer plat
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function delete(array $param): Response
    {
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            $this->dishService->deleteDish($_POST['id']);
            $confirmation_message = "'".$_POST['title']."' supprimé avec succès!";
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
        $_SESSION['AdminMenuController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? null
        ];
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/plats");
    }
    
    /**
     * updateOrder modifier l'ordre des plats (AJAX)
     *
     * @return Response
     */
    public function updateOrder(): Response
    {
        $http = 200;
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data) || !isset($data['order'])) {
            http_response_code(400);
            return $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ]);
        }
        try {
            $this->dishService->changeDishesOrder($data['order']);
            $_SESSION['confirmation_message'] = "Plats mis à jour avec succès!";
            return $this->json([
                'success' => true,
                'redirect' => "/admin/".$_SESSION['id']."/gestion/plats"
            ]);
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
        http_response_code($http);
        return $this->json([
            'success' => false,
            'message' => $error_message
        ]);
    }
}