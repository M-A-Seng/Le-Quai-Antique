<?php

namespace App\Core;

use App\Config\DbConnection;
use App\Controllers\AdminMenuController;
use App\Controllers\AdminReservationController;
use App\Controllers\AuthenticationController;
use App\Controllers\CategoryController;
use App\Controllers\DishController;
use App\Controllers\ErrorController;
use App\Controllers\GalleryController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\RedirectController;
use App\Controllers\RegistrationController;
use App\Controllers\ReservationController;
use App\Controllers\RestaurantServiceController;
use App\Controllers\SetMenuController;
use App\Controllers\UserController;
use App\Controllers\UserReservationController;
use App\Models\CategoryModel;
use App\Models\DishModel;
use App\Models\GalleryModel;
use App\Models\OpeningDayModel;
use App\Models\ReservationModel;
use App\Models\RestaurantModel;
use App\Models\RestaurantServiceModel;
use App\Models\ServiceModel;
use App\Models\SetMenuModel;
use App\Models\UserModel;
use App\Services\Api\CloudinaryService;
use App\Services\CategoryService;
use App\Services\DatetimeService;
use App\Services\DishService;
use App\Services\GalleryService;
use App\Services\OpeningDayService;
use App\Services\RenderService;
use App\Services\ReservationService;
use App\Services\RestaurantService;
use App\Services\RestaurantServiceService;
use App\Services\ServiceService;
use App\Services\SessionService;
use App\Services\SetMenuService;
use App\Services\UploadService;
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

    private RestaurantModel $restaurantModel;
    private RestaurantService $restaurantService;

    private ServiceModel $serviceModel;
    private ServiceService $serviceService;
    private RestaurantServiceModel $restaurantServiceModel;
    private RestaurantServiceService $restaurantServiceService;

    private OpeningDayModel $openingDayModel;
    private OpeningDayService $openingDayService;

    private ReservationModel $reservationModel;
    private ReservationService $reservationService;

    private CategoryModel $categoryModel;
    private CategoryService $categoryService;

    private DishModel $dishModel;
    private DishService $dishService;

    private SetMenuModel $setMenuModel;
    private SetMenuService $setMenuService;

    private CloudinaryService $cloudinary; # API
    private UploadService $uploadService;

    private GalleryModel $galleryModel;
    private GalleryService $galleryService;
    
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

        $this->restaurantModel = new RestaurantModel($this->backConnection);
        $this->restaurantService = new RestaurantService($this->restaurantModel);

        $this->serviceModel = new ServiceModel($this->backConnection);
        $this->restaurantServiceModel = new RestaurantServiceModel($this->backConnection);
        $this->serviceService = new ServiceService($this->serviceModel, $this->restaurantServiceModel, $this->datetimeService);
        $this->restaurantServiceService = new RestaurantServiceService($this->restaurantServiceModel, $this->serviceService, $this->datetimeService);
        
        $this->openingDayModel = new OpeningDayModel($this->backConnection);
        $this->openingDayService = new OpeningDayService($this->openingDayModel, $this->datetimeService);

        $this->reservationModel = new ReservationModel($this->frontConnection);
        $this->reservationService = new ReservationService($this->reservationModel, $this->datetimeService, $this->serviceService, $this->restaurantServiceModel, $this->openingDayService);

        $this->categoryModel = new CategoryModel($this->backConnection);
        $this->categoryService = new CategoryService($this->categoryModel, $this->restaurantModel);

        $this->dishModel = new DishModel($this->backConnection);
        $this->dishService = new DishService($this->dishModel, $this->categoryModel);

        $this->setMenuModel = new SetMenuModel($this->backConnection);
        $this->setMenuService = new SetMenuService($this->setMenuModel);

        $this->cloudinary = new CloudinaryService();
        $this->uploadService = new UploadService($this->cloudinary);

        $this->galleryModel = new GalleryModel($this->backConnection);
        $this->galleryService = new GalleryService($this->galleryModel, $this->uploadService);
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
        return new MenuController($this->dishService, $this->setMenuService, $this->renderService, $this->logger);
    }
    
    /**
     * getGalleryController retourne une instance de GalleryController.
     *
     * @return GalleryController
     */
    public function getGalleryController(): GalleryController
    {
        return new GalleryController($this->galleryService, $this->renderService, $this->logger);
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
        return new UserController($this->auth, $this->reservationService, $this->renderService, $this->logger);
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
     * getUserReservationController retourne une instance de UserReservationController
     *
     * @return UserReservationController
     */
    public function getUserReservationController(): UserReservationController
    {
        return new UserReservationController($this->reservationService, $this->renderService, $this->logger);
    }
    
    /**
     * getAdminReservationController retourne une instance de AdminReservationController
     *
     * @return AdminReservationController
     */
    public function getAdminReservationController(): AdminReservationController
    {
        return new AdminReservationController($this->reservationService, $this->serviceService, $this->renderService, $this->logger);
    }
    
    /**
     * getAdminMenuSettingsController retourne un instance de AdminMenuSettingsController
     *
     * @return AdminMenuController
     */
    public function getAdminMenuController(): AdminMenuController
    {
        return new AdminMenuController($this->categoryService, $this->dishService, $this->setMenuService, $this->renderService, $this->logger);
    }
    
    /**
     * getCategoryController retourne une instance de CategoryController
     *
     * @return CategoryController
     */
    public function getCategoryController(): CategoryController
    {
        return new CategoryController($this->categoryService, $this->dishService, $this->renderService, $this->logger);
    }
    
    /**
     * getDishController retourne une instance de DishController
     *
     * @return DishController
     */
    public function getDishController(): DishController
    {
        return new DishController($this->dishService, $this->renderService, $this->logger);
    }
    
    /**
     * getSetMenuController retourne une instance de SetMenuController
     *
     * @return SetMenuController
     */
    public function getSetMenuController(): SetMenuController
    {
        return new SetMenuController($this->setMenuService, $this->renderService, $this->logger);
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
    
    /**
     * getErrorController retourne une instance de ErrorController
     *
     * @return ErrorController
     */
    public function getErrorController(): ErrorController
    {
        return new ErrorController($this->renderService, $this->logger);
    }
}