<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Exceptions\RequireLoginException;
use App\Services\RenderService;

class UserController extends AbstractController
{
    private Auth $auth;

    public function __construct(Auth $auth, RenderService $renderService, Logger $logger)
    {
        parent::__construct($renderService, $logger);
        $this->auth = $auth;
    }

    public function loginClient(array $params): Response
    {
        if ((int)$params['id'] !== (int)$_SESSION['id']) {
            throw new RequireLoginException(UIMessage:"Votre session a expiré, veuillez vous reconnecter.");
        }
        $content = $this->renderService->render("profile");
        return $this->html($content);
    }

    public function loginAdmin(array $params): Response
    {
        if ((int)$params['id'] !== (int)$_SESSION['id']) {
            throw new RequireLoginException(UIMessage:"Votre session a expiré, veuillez vous reconnecter.");
        }
        $content = $this->renderService->render("admin");
        return $this->html($content);
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return $this->redirect('/');
    }
}