<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\RenderService;
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
    public function __construct(UserService $userService, Auth $auth, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->userService = $userService;
        $this->auth = $auth;
    }
    
    /**
     * index
     *
     * @return Response
     */
    public function index(): Response
    {
        $content = $this->renderService->render("signup");
        return $this->html($content);
    }
    
    /**
     * register
     *
     * @return Response
     */
    public function register(): Response
    {
        $error_message = '';
        $http = 200;
        try {
            unset($_POST['csrf_token']);
            $this->userService->signUserUp($_POST);
            $user = [
                'email' => $_POST['email'],
                'password' => $_POST['password'],
            ];
            $userData = $this->userService->authenticateUser($user);
            $this->auth->login($userData, true);

            return $this->redirect('/profil');
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

        $content = $this->renderService->render("signup", ["error_message" => $error_message]);
        return $this->html($content, $http);
    }
    
    /**
     * checkEmail vérifie si un email exist en db et communique avec AJAX.
     *
     * @return Response
     */
    public function checkEmail(): Response
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            return $this->json([
                'isValid' => false,
                'error_message' => 'Requête invalide'
            ], 400);
        }
        $email = $data['email'] ?? '';
        $isValid = true;
        $error_message = null;
        $http = 200;

        try {
            $this->userService->emailCheck($email, true);
        } 
        catch (AbstractFrontendException | NotFoundException $e) {
            $error_message = $e->getUIMessage();
            $isValid = false;
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

        $data = ['isValid' => $isValid, 
                'error_message' => $error_message];
        return $this->json($data, $http);
    }
}