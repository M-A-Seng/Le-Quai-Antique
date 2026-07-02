<?php

namespace App\Core\Abstract;

use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;

/**
 * AbstractController
 * 
 * - redirect()
 * - html()
 * - json()
 */
abstract class AbstractController
{    
    public function __construct(protected RenderService $renderService, protected Logger $logger) {}
    
    /**
     * redirect crée une réponse http de redirection.
     *
     * @param  string $url
     * @return Response
     */
    protected function redirect(string $url): Response
    {
        return new Response('', 302, ['Location' => $url]);
    }
    
    /**
     * html crée une réponse http pour une view.
     *
     * @param  mixed $content
     * @param  int $status
     * @return Response
     */
    protected function html(mixed $content, int $status = 200): Response
    {
        return new Response($content, $status, ['Content-Type' => 'text/html']);
    }

    /**
     * json crée une réponse http pour envoie de données en json
     *
     * @param  mixed $data
     * @param  int $status
     * @return Response
     */
    protected function json(mixed $data, int $status = 200): Response
    {
        return new Response(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 
                            $status, 
                            ['Content-Type' => 'application/json; charset=utf-8']);
    }
}