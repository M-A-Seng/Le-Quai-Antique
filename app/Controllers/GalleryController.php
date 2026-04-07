<?php 

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

class GalleryController extends AbstractController
{
    public function __construct(RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }

    public function index(): Response
    {
        $content = $this->renderService->render("gallery");
        return $this->html($content);
    }
}