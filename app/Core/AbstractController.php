<?php

namespace App\Core;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;

/**
 * AbstractController
 * 
 * - render()
 * - requirePostMethod()
 * - checkCsrfToken()
 */
abstract class AbstractController
{    
    /**
     * Méthode render() affiche le rendu d'une vue
     *
     * @param  mixed $view      | un fichier dans app/Views
     * @param  mixed $data      | un tableau de données pour les variables à injécter, vide par défaut
     * @param  mixed $layout    | structure html, "main" par défaut
     * @return void
     */
    protected function render(string $view, array $data = [], string $layout = "main")
    {
        extract($data, EXTR_SKIP);

        $viewPath   = __DIR__ . '/../Views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new NotFoundException(message: "Vue introuvable : $view");
        }
        if (!file_exists($layoutPath)) {
            throw new NotFoundException(message: "Layout introuvable : $layout");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require $layoutPath;
    }
        
    /**
     * requirePostMethod vérifie que la méthode http est POST, sinon recharge la page courante de l'utilisateur.
     *
     * @return void
     */
    protected function requirePostMethod(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

    /**
     * checkCsrfToken vérifie que l'utilisateur a un token csrf définit et que la requête http POST a le même token que la session.
     *
     * @return void
     */
    protected function checkCsrfToken(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new ServerException("Token CSRF invalide");
        }
    }
}

