<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use RuntimeException;

class UserController extends AbstractController
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function loginClient() 
    {
        $this->render("profile");
    }

    public function loginAdmin() 
    {
        $this->render("admin");
    }

    public function logout() 
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new RuntimeException("Token CSRF invalide");
        }
        $this->auth->logout();
        header("location: /");
        exit;
    }
}