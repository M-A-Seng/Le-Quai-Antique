<?php

namespace App\Core;

use App\Core\DIContainer;
use App\Core\Auth;
use App\Enums\Role;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\RequireLoginException;
use App\Exceptions\ServerException;
use App\Services\RenderService;

/**
 * Router 
 */
class Router
{
    private Auth $auth;
    /**
     * __construct
     *
     * @param  array $routes
     * @param  DIContainer $diContainer
     * @param string $env (dev / prod)
     * @return void
     */
    public function __construct(private array $routes, 
                                private DIContainer $diContainer, 
                                private RenderService $renderService, 
                                private string $env)
    {
        $this->auth = $this->diContainer->getAuth(); 
    }
        
    /**
     * dispatch exécute les méthodes de controller selon les requêtes entrées.
     *
     * @param  string $method
     * @param  string $uri
     * @return void
     */
    public function dispatch(string $method, string $uri): Response
    {
        foreach ($this->routes as $route)
        {
            [$httpMethod, $path, $controllerName, $controllerMethod] = $route;
            $middlewares = $route[4] ?? [];

            if ($method === $httpMethod && $uri === $path)
            {
                try {
                    if (!empty($middlewares)) {
                        foreach ($middlewares as $middleware) {
                            $this->runMiddleware($middleware);
                        }   
                    }
                } 
                catch (RequireLoginException $e) {
                    return new Response('', 302, ['Location' => '/connexion']);
                } 
                catch (ForbiddenException | ServerException $e) {
                    $content = $this->renderService->render((string)$e->getHttpCode(), [], 'error');
                    return new Response($content, $e->getHttpCode(), ['Content-Type' => 'text/html']);
                }

                $getController = "get" . $controllerName;

                if (!method_exists($this->diContainer, $getController)) {
                    if (APPENV === 'dev') {
                        throw new NotFoundException("Erreur : méthode '$getController' non trouvée dans le container");
                    } else {
                        $content = $this->renderService->render('404', [], 'error');
                        return new Response($content, 404, ['Content-Type' => 'text/html']);
                    }
                }
                $controller = $this->diContainer->$getController();
                $response = $controller->$controllerMethod();
                if (!$response instanceof Response) {
                    throw new ServerException("Le controller '$controller' doit retourner une instance de 'Response'");
                }
                return $response;
            }
        }
        $content = $this->renderService->render('404', [], 'error');
        return new Response($content, 404, ['Content-Type' => 'text/html']);
    }

    /**
     * validatePostAndCsrf vérifie que méthode HTTP = POST && que le token CSRF est valide.
     *
     * @return void
     */
    private function validatePostAndCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
        if (!isset($_POST['csrf_token']) || ($_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
            if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || ($_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token'])) {
                throw new ServerException("Token CSRF invalide");
            }
        }
    }
    
    /**
     * runMiddleware exécute des vérifications propres à certaines routes.
     *
     * @param  string $middleware
     * @return void
     */
    private function runMiddleware(string $middleware): void
    {
        switch($middleware)
        {
            case 'requireLogin':
                $this->auth->requireLogin();
                break;

            case 'requireClient':
                $this->auth->requireRole(Role::CLIENT);
                break;
                
            case 'requireAdmin':
                $this->auth->requireRole(Role::ADMIN);
                break;

            case 'requirePost&Csrf':
                $this->validatePostAndCsrf();
                break;

            default:
                throw new ServerException("Middleware inconnu: $middleware");
        }
    }
};