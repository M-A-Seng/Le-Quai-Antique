<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidCredentialsException;
use App\Services\UserService;
use InvalidArgumentException;
use RuntimeException;

class RegistrationController extends AbstractController
{
    private UserService $userService;
    private Auth $auth;
    
    public function __construct(UserService $userService, Auth $auth)
    {
        $this->userService = $userService;
        $this->auth = $auth;
    }

    public function index(): void
    {
        $this->render("signup");
    }

    public function register(): void
    {
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render("signup");
            return;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new RuntimeException("Token CSRF invalide");
        }

        try {
            $this->userService->signUserUp($_POST);
            $user = [
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'csrf_token' => $_POST['csrf_token']
            ];
            $userData = $this->userService->authenticateUser($user);
            $this->auth->login($userData, true);
            header("location: /profil");
            exit;
        } 
        catch (InvalidArrayForDbException $e) {
            $errorMessage = "Veuillez remplir les champs demandés.";
        }
        catch (InvalidArgumentException | InvalidCredentialsException $e) {
            $errorMessage = $e->getMessage();
        }
        catch (RuntimeException $e) {
            $errorMessage = $e->getMessage();
            # $errorMessage = "Erreur: Veuillez réessayer ou revenez plus tard.";
        }

        $this->render("signup", ["errorMessage" => $errorMessage]);
    }

    public function checkEmail()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $isValid = true;
        $errorMessage = '';

        try {
            $this->userService->emailCheck($email);
        } 
        catch (InvalidArgumentException $e) {
            $errorMessage = $e->getMessage();
            $isValid = false;
        }
        echo json_encode([
            'isValid' => $isValid,
            'errorMessage' => $errorMessage
        ]);
    }


}