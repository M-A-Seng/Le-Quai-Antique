<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

class UserController extends AbstractController
{
    private Auth $auth;

    public function __construct(Auth $auth, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->auth = $auth;
    }

    public function loginClient(): Response
    {
        $content = $this->renderService->render("profile");
        return $this->html($content);
    }

    public function loginAdmin(): Response
    {
        $content = $this->renderService->render("admin");
        return $this->html($content);
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return $this->redirect('/');
    }
}