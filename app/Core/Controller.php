<?php

namespace App\Core;

abstract class Controller
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
            throw new \Exception("Vue introuvable : $view");
        }
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout introuvable : $layout");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require $layoutPath;
    }
}

