<?php

namespace App\Controllers;

use App\Core\Abstract\AbstractController;
use App\Core\Response;

/**
 * ErrorController
 * 
 * - error404()
 * - error403()
 * - error500()
 */
class ErrorController extends AbstractController
{    
    /**
     * error404 page non trouvée
     *
     * @return Response
     */
    public function error404(): Response
    {
        $content = $this->renderService->render('400', [], 'error');
        return $this->html($content, 400);
    }
    
    /**
     * error403 accès refusé
     *
     * @return Response
     */
    public function error403(): Response
    {
        $content = $this->renderService->render('403', [], 'error');
        return $this->html($content, 403);
    }
    
    /**
     * error500 erreur interne serveur
     *
     * @return Response
     */
    public function error500(): Response
    {
        $content = $this->renderService->render('500', [], 'error');
        return $this->html($content, 500);
    }
}