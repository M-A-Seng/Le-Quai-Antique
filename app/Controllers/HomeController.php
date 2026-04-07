<?php 

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

class HomeController extends AbstractController
{
    public function __construct(RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    public function index(): Response
    {
        $content = $this->renderService->render("home");
        return $this->html($content);
    }
}