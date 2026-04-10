<?php

namespace App\Services;

use App\Exceptions\NotFoundException;

/**
 * RenderService
 * 
 * - render()
 */
class RenderService
{
    /**
     * Méthode render() affiche le rendu d'une vue
     * 
     * Si $testable = true; render() retourne la view dans `return` au lieu de retourner null.
     *
     * @param  string $view     | nom d'un fichier (sans extension) dans app/Views
     * @param  array $data      | un tableau de données pour les variables à injécter, vide par défaut
     * @param  string $layout   | structure html, "main" par défaut
     * @return string
     */
    public function render(string $view, array $data = [], string $layout = "main"): string
    {
        $data = $this->sanitizeStringForView($data);
        extract($data, EXTR_SKIP);

        $viewPath = $layout === 'error' ? 
            DIR_ROOT . '/app/Views/errors/' . $view . '.php'
            : DIR_ROOT . '/app/Views/' . $view . '.php';
        $layoutPath = DIR_ROOT . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new NotFoundException(message: "Vue introuvable : $view");
        }
        if (!file_exists($layoutPath)) {
            throw new NotFoundException(message: "Layout introuvable : $layout");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        ob_start();
        require $layoutPath;
        $html = ob_get_clean();

        return $html;
    }
    
    /**
     * sanitizeStringForView applique htmlspecialchars à tous les string d'un tableau
     *
     * @param  array $data
     * @return array
     */
    private function sanitizeStringForView(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        });
        return $data;
    }
}