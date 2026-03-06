<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use App\Enums\Role;
use App\Exceptions\InvalidCredentialsException;
use App\Services\UserService;
use InvalidArgumentException;
use RuntimeException;

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
    public function login()
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
            throw new RuntimeException("Token CSRF invalide");
        }

        try {
            $userData = $this->userService->authenticateUser($_POST);

            if (empty($userData['id']) || empty($userData['role'])) {
                throw new RuntimeException("Utilisateur invalide");
            }
            if ($userData['role'] !== 'CLIENT' && $userData['role'] !== 'ADMIN') {
                throw new RuntimeException("Rôle utilisateur inconnu.");
            }

            $this->auth->login($userData);

            $redirect = [
                'CLIENT' => '/profil',
                'ADMIN' => '/admin'
            ];
            header("location: " . $redirect[$userData['role']]);
            exit;
        } 
        catch (InvalidArgumentException | InvalidCredentialsException $e) {
            $errorMessage = $e->getMessage();
        } 
        catch (RuntimeException $e) {
            $errorMessage = "Erreur: Veuillez réessayer ou revenez plus tard.";
        }
        
        $this->render("login", ["errorMessage" => $errorMessage]);
    }
}