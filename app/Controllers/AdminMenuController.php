<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\CategoryService;
use App\Services\DishService;
use App\Services\RenderService;
use App\Services\SetMenuService;

/**
 * AdminMenuController
 * 
 * - index()
 */
class AdminMenuController extends AbstractController
{
    public function __construct(private CategoryService $categoryService,
                                private DishService $dishService, 
                                private SetMenuService $setMenusService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    /**
     * index gère l'affichage de la page de gestion de la carte du restaurant
     *
     * @param  array $params
     * @return Response
     */
    public function index(array $params): Response
    {
        $http = 200;
        $default = 'dishes';
        if (isset($params['branch'])) {
            switch ($params['branch']) {
                case 'categories':
                    $default = 'categories';
                    break;
            
                case 'plats':
                    $default = 'dishes';
                    break;
                
                case 'menus':
                    $default = 'setmenus';
                    break;

                default:
                    $content = $this->renderService->render('404', [], 'error');
                    return new Response($content, 404, ['Content-Type' => 'text/html']);
            }
        }
        $data = null;
        try {
            $data = [
                'default' => $default,
                'dishes' => $this->dishService->getRestaurantDishes(1),
                'categories' => $this->categoryService->getRestaurantCategories(1),
                'setmenus' => $this->setMenusService->getRestaurantMenus(1)
            ];
        }
        catch (AbstractFrontendException | NotFoundException $e) {
            $data['error_message'] = $e->getUIMessage();
        }
        catch (AbstractBackendException $e) {
            $data['error_message'] = $e->getUIMessage();
            $http = $e->getHttpCode();
            if ($e instanceof DbFailureException) {
                $this->logger->dbError($e->getMessage());
            } else {
                $this->logger->error($e->getMessage());
            }
        }
        if (isset($_SESSION['AdminMenuController_index'])) {
            $data = array_merge($data, $_SESSION['AdminMenuController_index']);
            $http = $_SESSION['AdminMenuController_index']['http'] ?? $http;
            unset($_SESSION['AdminMenuController_index']);
        }
        $content = $this->renderService->render('admin-menu/index', $data, 'user');
        return $this->html($content, $http);
    }
}