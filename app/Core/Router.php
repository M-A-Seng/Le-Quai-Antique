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

    public function __construct(private array $routes, 
                                private DIContainer $diContainer, 
                                private RenderService $renderService)
    {
        $this->auth = $this->diContainer->getAuth(); 
    }
        
    /**
     * dispatch exécute les méthodes de controller selon les requêtes entrées.
     *
     * @param  string $method
     * @param  string $uri
     * @return Response
     */
    public function dispatch(string $method, string $uri): Response
    {
        foreach ($this->routes as $route)
        {
            [$httpMethod, $path, $controllerName, $controllerMethod] = $route;
            $middlewares = $route[4] ?? [];

            if ($method !== $httpMethod) {
                continue; // saute les méthodes http qui ne correspondent pas
            }

            $paramNames = [];
            # remplace les paramètres {?} dans $path par un regex
            $regex = preg_replace_callback(
                '#\{([^}]+)\}#', # Cherche ce regex dans $path
                function ($matches) use (&$paramNames) {
                    $paramNames[] = $matches[1]; # Récupère le nom du paramètre dans $paramNames
                    return '([^/]+)'; # Remplace le paramètre par regex
                },
                $path
            );
            $regex = '#^' . $regex . '/?$#'; # regex strict, /path/{param} devient #^/path/([^/]+)/?$#

            # Compare $path et $uri et extrait les valeurs du regex->paramètre dans $matches
            if (!preg_match($regex, $uri, $matches)) {
                continue; # Route suivante si ça ne marche pas
            }
            array_shift($matches); // retire $matches[0] (chemin complet de $uri)
            $params = array_combine($paramNames, $matches); # Tableau associatif 'param' => 'value'

            try {
                if (!empty($middlewares)) {
                    foreach ($middlewares as $middleware) {
                        $this->runMiddleware($middleware);
                    }   
                }
            }
            catch (RequireLoginException $e) {
                $content = $this->renderService->render('login', ["error_message" => "Votre session a expiré. Veuillez vous reconnecter."]);
                return new Response($content, 302, ['Content-Type' => 'text/html']);
            } 
            catch (ForbiddenException | ServerException $e) {
                $content = $this->renderService->render((string)$e->getHttpCode(), [], 'error');
                return new Response($content, $e->getHttpCode(), ['Content-Type' => 'text/html']);
            }

            $getController = "get" . $controllerName;

            if (!method_exists($this->diContainer, $getController)) {
                if (APPENV === 'dev') {
                    throw new NotFoundException(__METHOD__ . ": Erreur : méthode '$getController' non trouvée dans le container");
                } else {
                    $content = $this->renderService->render('404', [], 'error');
                    return new Response($content, 404, ['Content-Type' => 'text/html']);
                }
            }
            $controller = $this->diContainer->$getController();
            $response = $controller->$controllerMethod(param:$params);
            if (!$response instanceof Response) {
                throw new ServerException(__METHOD__ . ": Le controller '$controller' doit retourner une instance de 'Response'");
            }
            return $response;
        }
        $content = $this->renderService->render('404', [], 'error');
        return new Response($content, 404, ['Content-Type' => 'text/html']);
    }

    /**
     * validatePostAndCsrf vérifie que méthode HTTP = POST && que le token CSRF est valide.
     *
     * @return ?Response
     */
    private function validatePostAndCsrf(): ?Response
    {
        $error_message = "Votre session a expiré ou la requête est invalide. Veuillez réessayer.";

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = $error_message;
            return new Response('', 303, ['Location' => $_SERVER['REQUEST_URI']]);
        }

        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$csrfToken || $csrfToken !== $_SESSION['csrf_token']) {
            $_SESSION['error_message'] = $error_message;
            return new Response('', 303, ['Location' => $_SERVER['REQUEST_URI']]);
        }
        return null;
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
                throw new ServerException(__METHOD__ . ": Middleware inconnu: $middleware");
        }
    }
};