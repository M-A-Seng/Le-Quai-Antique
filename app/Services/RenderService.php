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
     * 
     * @throws NotFoundException
     */
    public function render(string $view, array $data = [], string $layout = "main"): string
    {
        $data['error_message'] = $this->getFlashMessage('error_message') ?? $data['error_message'] ?? null;
        $data['confirmation_message'] = $this->getFlashMessage('confirmation_message') ?? $data['confirmation_message'] ?? null;
        
        extract($data, EXTR_SKIP);

        $viewPath = $layout === 'error' ? 
            DIR_ROOT . '/app/Views/errors/' . $view . '.php'
            : DIR_ROOT . '/app/Views/' . $view . '.php';
        $layoutPath = APP_PROTECTED === 'true' ?
            DIR_ROOT . '/app/Views/layouts/protected.php'
            : DIR_ROOT . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new NotFoundException(message: __METHOD__ . ": Vue introuvable : $view");
        }
        if (!file_exists($layoutPath)) {
            throw new NotFoundException(message: __METHOD__ . ": Layout introuvable : $layout");
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
     * getFlashMessage Retourne le message flash stocké dans la session de l'utilisateur.
     *
     * @param  string $key
     * @return string
     */
    private function getFlashMessage(string $key): ?string
    {
        if (!empty($_SESSION[$key])) 
        {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }
}