<?php

namespace App\Core;

use App\Core\DIContainer;

class Router
{
    private array $routes;
    private DIContainer $diContainer;
    
    /**
     * Constructeur recevant un tableau de routes pour l'attribuer à la variable $routes
     *
     * @param array<int, array{0:string,1:string,2:string,3:string}> $routes
     * @return void
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes; 
        $this->diContainer = new DIContainer;
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
        foreach ($this->routes as [$httpMethod, $path, $controllerName, $controllerMethod])
        {
            if ($method === $httpMethod && $uri === $path)
            {
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
};