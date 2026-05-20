<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\CategoryService;
use App\Services\RenderService;

class AdminMenuSettingsController extends AbstractController
{
    public function __construct(private CategoryService $categoryService, 
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }

    public function index(array $params): Response
    {
        // if (isset($params['branch'])) {
        //     switch ($params['branch']) {
        //         case 'categories':
        //             # code...
        //             break;
                
        //         case 'plats':
        //             # code...
        //             break;
                
        //         case 'menus':
        //             # code...
        //             break;

        //         default:
        //             $content = $this->renderService->render('404', [], 'error');
        //             return new Response($content, 404, ['Content-Type' => 'text/html']);
        //     }
        // }
        
        $categories = $this->categoryService->getRestaurantCategories(1);

        $data = [
            'categories' => $categories,
        ];
        $content = $this->renderService->render('admin.menu', $data, 'user');
        return $this->html($content);
    }
}