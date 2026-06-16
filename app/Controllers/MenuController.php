<?php 

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\AbstractBackendException;
use App\Exceptions\AbstractFrontendException;
use App\Exceptions\DbFailureException;
use App\Exceptions\NotFoundException;
use App\Services\DishService;
use App\Services\RenderService;
use App\Services\SetMenuService;

/**
 * MenuController
 * 
 * - index()
 */
class MenuController extends AbstractController
{
    public function __construct(private DishService $dishService, 
                                private SetMenuService $setMenusService,
                                RenderService $renderService, 
                                Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
        
    /**
     * index afficher carte du restaurant
     *
     * @return Response
     */
    public function index(): Response
    {
        $data = null;
        $http = 200;
        try {
            $dishes = $this->dishService->getRestaurantDishes(1);
            unset($dishes['Assiettes non catégorisées']);
            $data = [
                'page' => 'la-carte',
                'dishes' => $dishes,
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
        $content = $this->renderService->render("menu", $data);
        return $this->html($content, $http);
    }
}