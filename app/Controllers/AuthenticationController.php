<?php

namespace App\Controllers;

use App\Core\AbstractController;
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
    
    /**
     * __construct
     *
     * @param  UserService $userService
     * @return void
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render("login");
            return;
        }

        try {
            $userRole = $this->userService->authenticateUser($_POST);

            if ($userRole === 'CLIENT') {
                $this->render("profile");
                return;
            } 
            if ($userRole === 'ADMIN') {
                $this->render("admin");
                return;
            } 
            throw new RuntimeException("Rôle utilisateur inconnu.");
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