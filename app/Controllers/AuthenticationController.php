<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Services\UserService;

/**
 * AuthenticationController gère le système de connexion utilisateur.
 */
class AuthenticationController extends AbstractController
{
    private UserService $userService;
    private Auth $auth;
      
    /**
     * __construct
     *
     * @param  UserService $userService
     * @param  SessionService $session
     * @return void
     */
    public function __construct(UserService $userService, Auth $auth)
    {
        $this->userService = $userService;
        $this->auth = $auth;
    }
    
    /**
     * login dirige vers la page de connexion.
     *
     * @return void
     */
    public function index()
    {
        $this->render("login");
    }
    
    /**
     * authenticate identifie l'utilisateur et dirige vers la page appropriée.
     *
     * @return void
     */
    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render("login");
            return;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new ServerException("Token CSRF invalide");
        }

        try {
            $userData = $this->userService->authenticateUser($_POST);

            if (empty($userData['id']) || empty($userData['role'])) {
                throw new ForbiddenException("Utilisateur non reconnu.");
            }
            if ($userData['role'] !== 'CLIENT' && $userData['role'] !== 'ADMIN') {
                throw new ForbiddenException("Rôle utilisateur inconnu.");
            }

            $this->auth->login($userData);

            $redirect = [
                'CLIENT' => '/profil',
                'ADMIN' => '/admin'
            ];
            header("location: " . $redirect[$userData['role']]);
            exit;
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            http_response_code($e->getHttpCode());
            $errorMessage = $e->getUIMessage();
            error_log($e->getMessage());
        }
        
        $this->render("login", ["errorMessage" => $errorMessage]);
    }
}