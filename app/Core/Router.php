<?php

namespace App\Core;

use App\Core\DIContainer;
use App\Core\Auth;
use App\Enums\Role;
use App\Exceptions\NotFoundException;

/**
 * Router 
 */
class Router
{
    private array $routes;
    private DIContainer $diContainer;
    private Auth $auth;
    
    /**
     * __construct
     *
     * @param  array $routes
     * @param  DIContainer $diContainer
     * @return void
     */
    public function __construct(array $routes, DIContainer $diContainer)
    {
        $this->routes = $routes; 
        $this->diContainer = $diContainer;
        $this->auth = $this->diContainer->getAuth(); 
    }
        
    /**
     * dispatch exécute les méthodes de controller selon les requêtes entrées.
     *
     * @param  string $method
     * @param  string $uri
     * @return void
     */
    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route)
        {
            [$httpMethod, $path, $controllerName, $controllerMethod] = $route;
            $middlewares = $route[4] ?? [];

            if ($method === $httpMethod && $uri === $path)
            {
                if ($middlewares) {
                    foreach ($middlewares as $middleware) {
                        $this->runMiddleware($middleware);
                    }   
                }

                $getController = "get" . $controllerName;

                if (!method_exists($this->diContainer, $getController)) {
                    http_response_code(500);
                    # Retirer echo en prod
                    echo "Erreur : méthode $getController non trouvée dans le container";
                    return;
                }

                $controller = $this->diContainer->$getController();
                $controller->$controllerMethod();
                return;
            }
        }
        http_response_code(404);
        echo '404 - Page non trouvée';
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

            default:
                throw new NotFoundException("Middleware inconnu: $middleware");
        }
    }
};