<?php

namespace App\Controllers;

/**
 * RedirectController renvoie un code http 301 pour rediriger l'utilisateur vers l'url officiel du site
 */
class RedirectController
{
    public function home(): void
    {
        header('Location: /', true, 301);
        exit;
    }

    public function menu(): void
    {
        header('Location: /la-carte', true, 301);
        exit;
    }

    public function gallery(): void
    {
        header('Location: /galerie', true, 301);
        exit;
    }

    public function signup(): void
    {
        header('Location: /inscription', true, 301);
        exit;
    }

    public function login(): void
    {
        header('Location: /connexion', true, 301);
        exit;
    }
}
