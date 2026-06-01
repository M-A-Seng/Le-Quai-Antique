<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Services\RenderService;
use App\Services\UserService;

/**
 * AuthenticationController gère le système de connexion utilisateur.
 * 
 * - index()
 * - authenticate()
 * - provideDevAccess()
 */
class AuthenticationController extends AbstractController
{
    private UserService $userService;
    private Auth $auth;
      
    public function __construct(UserService $userService, Auth $auth, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->userService = $userService;
        $this->auth = $auth;
    }
    
    /**
     * login dirige vers la page de connexion.
     *
     * @return Response
     */
    public function index(): Response
    {
        $content = $this->renderService->render("login");
        return $this->html($content);
    }
    
    /**
     * authenticate identifie l'utilisateur et dirige vers la page appropriée.
     *
     * @return Response
     */
    public function authenticate(): Response
    {
        $http = 200;
        try {
            unset($_POST['csrf_token']);
            $userData = $this->userService->authenticateUser($_POST);

            if (empty($userData['id']) || empty($userData['role'])) {
                throw new ForbiddenException(__METHOD__ . ": Utilisateur non reconnu.");
            }
            if ($userData['role'] !== 'CLIENT' && $userData['role'] !== 'ADMIN') {
                throw new ForbiddenException(__METHOD__ . ": Rôle utilisateur inconnu : " . $userData['role']);
            }

            $this->auth->login($userData);

            $redirect = ['CLIENT' => '/profil/' . $userData['id'],
                         'ADMIN' => '/admin/' . $userData['id']];
            return $this->redirect($redirect[$userData['role']]);
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
        $content = $this->renderService->render("login", ["error_message" => $error_message]);
        return $this->html($content, $http);
    }
    
    /**
     * provideDevAccess environnement preprod
     *
     * @return Response
     */
    public function provideDevAccess(array $params): Response
    {
        if (empty($_POST['csrf_token']) || empty($_POST['access_key']) ) {
            $_SESSION['error_message'] = "Invalid request";
            return $this->redirect('/');
        }
        if (password_verify($_POST['access_key'], '$2y$12$9Xp9iiqAuEriJKZkV1BOZ.d.lBnUD0mkr7Alg8Og6E8TIdS/WgSLe')) {
            $_SESSION['dev_token'] = bin2hex(random_bytes(32));
            $_SESSION['last_activity'] = time();
        } else {
            $_SESSION['error_message'] = "Invalid key";
        }
        return $this->redirect('/');
    }
}