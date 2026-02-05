<?php

namespace App\Core;

class Router
{
    private array $routes;
    
    /**
     * Constructeur recevant un tableau de routes pour l'attribuer à la variable $routes
     *
     * @param array<int, array{0:string,1:string,2:string,3:string}> $routes
     * @return void
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes; 
    }
    
    /**
     * Méthode dispatch() gère les controllers à exécuter selon les requêtes client
     *
     * @param  string $method   | méthode http demandé
     * @param  string $uri      | chemin demandé par le client
     * @return void             | stop le flux d'exécution après le IF
     */
    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as [$httpMethod, $path, $controllerName, $controllerMethod])
        {
            if ($method === $httpMethod && $uri === $path)
            {
                $controller = 'App\Controllers\\' . $controllerName;

                (new $controller)->$controllerMethod();
                return;
            }
        }
        http_response_code(404);
        echo '404 - Page non trouvée';
    }
};