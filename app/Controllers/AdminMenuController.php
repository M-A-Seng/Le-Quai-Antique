<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\CategoryService;
use App\Services\DishService;
use App\Services\RenderService;

/**
 * AdminMenuController
 * 
 * - index()
 */
class AdminMenuController extends AbstractController
{
    public function __construct(private CategoryService $categoryService,
                                private DishService $dishService, 
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
        $dishes = $this->dishService->getAllRestaurantDishes(1);
        $categories = $this->categoryService->getRestaurantCategories(1);

        $data = [
            'default' => $default,
            'dishes' => $dishes,
            'categories' => $categories,
        ];
        if (isset($_SESSION['AdminMenuController_index'])) {
            $data = array_merge($data, $_SESSION['AdminMenuController_index']);
            $http = $_SESSION['AdminMenuController_index']['http'] ?? $http;
            unset($_SESSION['AdminMenuController_index']);
        }
        $content = $this->renderService->render('admin-menu/index', $data, 'user');
        return $this->html($content, $http);
    }
}