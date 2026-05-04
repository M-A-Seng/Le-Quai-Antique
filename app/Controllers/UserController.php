<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
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
        # Si l'utilisateur a rempli le formulaire de réservation avant de s'authentifier
        if (isset($_SESSION['reservation_pending_confirmation']) && $_SESSION['reservation_pending_confirmation']) {
            return $this->redirect('/reserver/confirmation');
        }
        $content = $this->renderService->render("user.profile");
        return $this->html($content);
    }

    public function loginAdmin(array $params): Response
    {
        if ((int)$params['id'] !== (int)$_SESSION['id']) {
            throw new RequireLoginException(UIMessage:"Votre session a expiré, veuillez vous reconnecter.");
        }
        # Si l'utilisateur a rempli le formulaire de réservation avant de s'authentifier
        if (isset($_SESSION['reservation_pending_confirmation']) && $_SESSION['reservation_pending_confirmation']) {
            return $this->redirect('/reserver/confirmation');
        }
        return $this->redirect('/admin/'.$_SESSION['id'].'/reservations');
    }

    public function logout(): Response
    {
        $this->auth->logout();
        return $this->redirect('/');
    }
}