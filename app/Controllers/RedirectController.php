<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

/**
 * RedirectController renvoie un code http 301 pour rediriger l'utilisateur vers l'url officiel du site
 */
class RedirectController extends AbstractController
{
    public function __construct(RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
    }
    
    public function home(): Response
    {
        return $this->redirect('/');
    }

    public function menu(): Response
    {
        return $this->redirect('/la-carte');
    }

    public function gallery(): Response
    {
        return $this->redirect('/galerie');
    }

    public function signup(): Response
    {
        return $this->redirect('/inscription');
    }

    public function login(): Response
    {
        return $this->redirect('/connexion');
    }
}
