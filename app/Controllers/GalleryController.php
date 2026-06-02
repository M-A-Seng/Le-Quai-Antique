<?php 

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Services\GalleryService;
use App\Services\RenderService;

/**
 * GalleryController
 * 
 * - index()
 * - upload()
 * - update()
 * - updateOrder()
 * - delete()
 */
class GalleryController extends AbstractController
{
    public function __construct(private GalleryService $galleryService, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * index afficher galerie
     *
     * @param  array $param
     * @return Response
     */
    public function index(?array $param = null): Response
    {
        $result = $this->galleryService->getRestaurantImages(1);
        $data = [
            "images" => $result
        ];
        if (isset($_SESSION['GalleryController_index'])) {
            $data = array_merge($data, $_SESSION['GalleryController_index']);
            unset($_SESSION['GalleryController_index']);
        }
        $layout = isset($_SESSION['id'], $_SESSION['role']) && $_SESSION['role']->value === 'ADMIN' ? 'user' : 'main';
        $content = $this->renderService->render("gallery", $data, $layout);
        return $this->html($content, $data['http'] ?? 200);
    }
    
    /**
     * upload importer image
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function upload(array $param): Response
    {
        if (((int)$param['id'] !== (int)$_SESSION['id']) || ($_SESSION['role']->value !== 'ADMIN')) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            $this->galleryService->newImage(1, $_POST, $_FILES['image']);
            $confirmation_message = "Image '".$_POST['title']."' ajoutée avec succès!";
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
        $_SESSION['GalleryController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? 200
        ];
        return $this->redirect('/galerie');
    }
    
    /**
     * update modifier image
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function update(array $param): Response
    {
        if (((int)$param['id'] !== (int)$_SESSION['id']) || ($_SESSION['role']->value !== 'ADMIN')) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $this->galleryService->modifyImage($_POST, $_FILES['image']);
                $confirmation_message = "Image '".$_POST['title']."' modifiée avec succès! La mise à jour de la galerie peut prendre quelques minutes.";
            } else {
                $this->galleryService->modifyImage($_POST);
                $confirmation_message = "Image '".$_POST['title']."' modifiée avec succès!";
            }
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
        $_SESSION['GalleryController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? 200
        ];
        return $this->redirect('/galerie');
    }
    
    /**
     * updateOrder changer l'ordre des images
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function updateOrder(array $param): Response
    {
        if (!isset($_SESSION['id']) || ($_SESSION['role']->value !== 'ADMIN')) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
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
            $this->galleryService->changeImagesOrder($data['order']);
            $_SESSION['confirmation_message'] = "Galerie mise à jour avec succès!";
            return $this->json([
                'success' => true,
                'redirect' => "/galerie"
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
    
    /**
     * delete supprimer image
     *
     * @param  array $param
     * @return Response
     * 
     * @throws ForbiddenException
     */
    public function delete(array $param): Response
    {
        if (((int)$param['id'] !== (int)$_SESSION['id']) || ($_SESSION['role']->value !== 'ADMIN')) {
            throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
        }
        unset($_POST['csrf_token']);
        try {
            $this->galleryService->deleteImage($_POST['id']);
            $confirmation_message = "Image '".$_POST['title']."' supprimée avec succès!";
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
        $_SESSION['GalleryController_index'] = [
            'confirmation_message' => $confirmation_message ?? null,
            'error_message' => $error_message ?? null,
            'http' => $http ?? 200
        ];
        return $this->redirect('/galerie');
    }
}