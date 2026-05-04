<?php

namespace App\Core;

use App\Config\DbConnection;
use App\Controllers\AuthenticationController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\RedirectController;
use App\Controllers\RegistrationController;
use App\Controllers\ReservationController;
use App\Controllers\RestaurantServiceController;
use App\Controllers\UserController;
use App\Models\OpeningDayModel;
use App\Models\ReservationModel;
use App\Models\RestaurantServiceModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Services\DatetimeService;
use App\Services\OpeningDayService;
use App\Services\RenderService;
use App\Services\ReservationService;
use App\Services\RestaurantServiceService;
use App\Services\ServiceService;
use App\Services\SessionService;
use App\Services\UserService;

/**
 * DIContainer - Dependencies Injection Container.
 */
class DIContainer 
{
    private Logger $logger;
    private DatetimeService $datetimeService;

    private PdoFactory $pdoFactory;
    private DbConnection $frontConnection;
    private DbConnection $backConnection;
    private DbConnection $logsConnection;

    private SessionService $sessionService;
    private Auth $auth;

    private UserModel $userModel;
    private UserService $userService;

    private ServiceModel $serviceModel;
    private ServiceService $serviceService;
    private RestaurantServiceModel $restaurantServiceModel;
    private RestaurantServiceService $restaurantServiceService;

    private OpeningDayModel $openingDayModel;
    private OpeningDayService $openingDayService;

    private ReservationModel $reservationModel;
    private ReservationService $reservationService;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(private RenderService $renderService)
    {
        $this->logger = new Logger('app.log');
        $this->datetimeService = new DatetimeService();

        $this->pdoFactory = new PdoFactory();
        $this->frontConnection = new DbConnection('front', $this->pdoFactory, $this->logger);
        $this->backConnection = new DbConnection('back', $this->pdoFactory, $this->logger);
        $this->logsConnection = new DbConnection('logs', $this->pdoFactory, $this->logger);

        $this->sessionService = new SessionService();
        $this->auth = new Auth($this->sessionService);

        $this->userModel = new UserModel($this->frontConnection);
        $this->userService = new UserService($this->userModel);

        $this->serviceModel = new ServiceModel($this->backConnection);
        $this->restaurantServiceModel = new RestaurantServiceModel($this->backConnection);
        $this->serviceService = new ServiceService($this->serviceModel, $this->restaurantServiceModel, $this->datetimeService);
        $this->restaurantServiceService = new RestaurantServiceService($this->restaurantServiceModel, $this->serviceService, $this->datetimeService);
        
        $this->openingDayModel = new OpeningDayModel($this->backConnection);
        $this->openingDayService = new OpeningDayService($this->openingDayModel, $this->datetimeService);

        $this->reservationModel = new ReservationModel($this->frontConnection);
        $this->reservationService = new ReservationService($this->reservationModel, $this->datetimeService, $this->serviceService, $this->restaurantServiceModel, $this->openingDayService);
    }
        
    /**
     * getRouter retourne une instance du Router.
     *
     * @param  array $routes
     * @param  DIContainer $diContainer
     * @return Router
     */
    public function getRouter($routes, $diContainer): Router
    {
        return new Router($routes, $diContainer, $this->renderService);
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
        return new HomeController($this->renderService, $this->logger);
    }
    
    /**
     * getMenuController retourne une instance de MenuController.
     *
     * @return MenuController
     */
    public function getMenuController(): MenuController
    {
        return new MenuController($this->renderService, $this->logger);
    }
    
    /**
     * getGalleryController retourne une instance de GalleryController.
     *
     * @return GalleryController
     */
    public function getGalleryController(): GalleryController
    {
        return new GalleryController($this->renderService, $this->logger);
    }
    
    /**
     * getAuthenticationController retourne une instance de AuthenticationController.
     *
     * @return AuthenticationController
     */
    public function getAuthenticationController(): AuthenticationController
    {
        return new AuthenticationController($this->userService, $this->auth, $this->renderService, $this->logger);
    }
    
    /**
     * getRegistrationController retourne une instance de RegistrationController
     *
     * @return RegistrationController
     */
    public function getRegistrationController(): RegistrationController
    {
        return new RegistrationController($this->userService, $this->auth, $this->renderService, $this->logger);
    }

    /**
     * getUserController retourne une instance de UserController.
     *
     * @return UserController
     */
    public function getUserController(): UserController
    {
        return new UserController($this->auth, $this->renderService, $this->logger);
    }
        
    /**
     * getRestaurantController retourne une instance de RestaurantController.
     *
     * @return RestaurantServiceController
     */
    public function getRestaurantServiceController(): RestaurantServiceController
    {
        return new RestaurantServiceController($this->restaurantServiceService, $this->renderService, $this->logger);
    }
    
    /**
     * getReservationController retourne une instance de ReservationController
     *
     * @return ReservationController
     */
    public function getReservationController(): ReservationController
    {
        return new ReservationController($this->reservationService, $this->userService, $this->renderService, $this->logger);
    }

    /**
     * getRedirectController retourne une instance de RedirectController.
     *
     * @return RedirectController
     */
    public function getRedirectController(): RedirectController
    {
        return new RedirectController($this->renderService, $this->logger);
    }
}