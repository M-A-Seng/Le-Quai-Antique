<?php 

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

/**
 * HomeController
 * 
 * - index()
 */
class HomeController extends AbstractController
{
    public function __construct(RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
        
    /**
     * index page d'accueil
     *
     * @return Response
     */
    public function index(): Response
    {
        $content = $this->renderService->render("home", ['page' => 'accueil']);
        return $this->html($content);
    }
}