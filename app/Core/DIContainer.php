<?php

namespace App\Core;

use App\Config\DbConnection;
use App\Controllers\AuthenticationController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\RedirectController;
use App\Controllers\UserController;
use App\Models\UserModel;
use App\Services\SessionService;
use App\Services\UserService;

/**
 * DIContainer - Dependencies Injection Container.
 */
class DIContainer 
{
    private DbConnection $frontConnection;
    private DbConnection $backConnection;
    private DbConnection $logsConnection;

    private SessionService $sessionService;
    private Auth $auth;

    private UserModel $userModel;
    private UserService $userService;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->frontConnection = new DbConnection('front');
        $this->backConnection = new DbConnection('back');
        $this->logsConnection = new DbConnection('logs');

        $this->sessionService = new SessionService();
        $this->auth = new Auth($this->sessionService);

        $this->userModel = new UserModel($this->frontConnection);
        $this->userService = new UserService($this->userModel, $this->sessionService);
    }
    
    /**
     * getAuth retourne une instance de Auth.
     *
     * @return Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }
    
    /**
     * getHomeController retourne une instance de HomeController.
     *
     * @return HomeController
     */
    public function getHomeController(): HomeController
    {
        return new HomeController();
    }
    
    /**
     * getMenuController retourne une instance de MenuController.
     *
     * @return MenuController
     */
    public function getMenuController()
    {
        return new MenuController();
    }
    
    /**
     * getGalleryController retourne une instance de GalleryController.
     *
     * @return GalleryController
     */
    public function getGalleryController()
    {
        return new GalleryController();
    }
    
    /**
     * getAuthenticationController retourne une instance de AuthenticationController.
     *
     * @return AuthenticationController
     */
    public function getAuthenticationController(): AuthenticationController
    {
        return new AuthenticationController($this->userService, $this->auth);
    }
        
    /**
     * getClientController retourne une instance de ClientController.
     *
     * @return ClientController
     */
    public function getUserController(): UserController
    {
        return new UserController($this->auth);
    }

    /**
     * getRedirectController retourne une instance de RedirectController.
     *
     * @return RedirectController
     */
    public function getRedirectController(): RedirectController
    {
        return new RedirectController();
    }
}