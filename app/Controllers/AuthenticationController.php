<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Services\UserService;

class AuthenticationController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login()
    {
        $this->render("login");
    }

    public function authenticate()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            $data = $_POST;

            $user = $this->userService->authenticateUser($data);

            if ($user === 'CLIENT') {
                $this->render("profile");
                return;
            } 
            elseif ($user === 'ADMIN') {
                $this->render("admin");
                return;
            } 
            else {
                echo "Erreur: ";
            }
        }
        
        
        
    }
}