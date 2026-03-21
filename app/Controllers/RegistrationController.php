<?php

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Auth;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\NotFoundException;
use App\Services\UserService;

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
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            http_response_code($e->getHttpCode());
            $errorMessage = $e->getUIMessage();
            error_log($e->getMessage());
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
        catch (AbstractFrontendException | NotFoundException $e) {
            $errorMessage = $e->getUIMessage();
            $isValid = false;
        }
        catch (AbstractBackendException $e) {
            http_response_code($e->getHttpCode());
            $errorMessage = $e->getUIMessage();
            error_log($e->getMessage());
        }
        echo json_encode([
            'isValid' => $isValid,
            'errorMessage' => $errorMessage
        ]);
    }
}