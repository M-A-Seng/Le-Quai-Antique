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
use App\Services\CategoryService;
use App\Services\DishService;

/**
 * CategoryController
 * 
 * - create()
 * - update()
 * - delete()
 * - canDelete()
 * - updateOrder()
 */
class CategoryController extends AbstractController
{
    public function __construct(private CategoryService $categoryService, 
                                private DishService $dishService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * create nouvelle catégorie
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
        $http = 200;
        unset($_POST['csrf_token']);
        try {
            $this->categoryService->newCategory(1, $_POST);
            $_SESSION['confirmation_message'] = "Catégorie '".$_POST['title']."' créée avec succès!";
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $_SESSION['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $_SESSION['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/categories");
    }
    
    /**
     * update modifier une catégorie
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
        $http = 200;
        unset($_POST['csrf_token']);
        try {
            $this->categoryService->updateCategory($_POST);
            $_SESSION['confirmation_message'] = "Catégorie modifiée avec succès!";
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $_SESSION['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $_SESSION['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/categories");
    }
    
    /**
     * delete supprimer une catégorie
     *
     * @param  mixed $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function delete(array $param): Response
    {
        if ((int)$param['id'] !== (int)$_SESSION['id']) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        $http = 200;
        try {
            $this->categoryService->removeCategory($_POST['id']);
            $_SESSION['confirmation_message'] = "Catégorie '".$_POST['title']."' supprimée avec succès!";
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
        return $this->redirect("/admin/".$_SESSION['id']."/gestion/categories");
    }
    
    /**
     * canDelete vérifie si une catégorie est vide pour supprimer (AJAX)
     *
     * @return Response
     */
    public function canDelete(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data) || empty($data['id'])) {
            return $this->json([
                'error_message' => 'Requête invalide'
            ], 400);
        }
        $dishes = null;
        try {
            $dishes = $this->dishService->getDishesInCategory(1, $data['id']);
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
        $data = $dishes === null || empty($dishes) ? ['can_delete' => true] : $dishes;
        return $this->json($data);
    }
    
    /**
     * updateOrder modifie l'ordre des catégories (AJAX)
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
            $this->categoryService->changeCategoriesOrder($data['order']);
            $_SESSION['confirmation_message'] = "Catégories mises à jour avec succès!";
            return $this->json([
                'success' => true,
                'redirect' => "/admin/".$_SESSION['id']."/gestion/categories"
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