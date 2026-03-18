<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidCredentialsException;
use App\Services\UserService;
use InvalidArgumentException;
use RuntimeException;

/**
 * RegistrationController
 * 
 * - index()
 * - register()
 * - checkEmail()
 */
class RegistrationController extends AbstractController
{
    private UserService $userService;
    private Auth $auth;
        
    /**
     * __construct
     *
     * @param  UserService $userService
     * @param  Auth $auth
     * @return void
     */
    public function __construct(UserService $userService, Auth $auth)
    {
        $this->userService = $userService;
        $this->auth = $auth;
    }
    
    /**
     * index
     *
     * @return void
     */
    public function index(): void
    {
        $this->render("signup");
    }
    
    /**
     * register
     *
     * @return void
     */
    public function register(): void
    {
        $this->requirePostMethod();
        $this->checkCsrfToken();
        $errorMessage = '';
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
            $errorMessage = "Erreur: Veuillez réessayer ou revenez plus tard.";
        }

        $this->render("signup", ["errorMessage" => $errorMessage]);
    }
    
    /**
     * checkEmail vérifie si un email exist en db et communique avec AJAX.
     *
     * @return void
     */
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