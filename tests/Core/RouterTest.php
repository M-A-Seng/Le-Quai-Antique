<?php

namespace App\Tests\Core;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Core\Auth;
use App\Core\DIContainer;
use App\Core\Response;
use App\Core\Router;
use App\Exceptions\NotFoundException;
use App\Services\RenderService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validRoutesProvider(): array {
        return [
            'clé manquante (strict)' => [],
            'clé vide (strict)' => [],
            'clé null (non strict)' => [],
            'clé manquante (strict)' => [],
            'clé vide (strict)' => [],
            'clé null (non strict)' => [],
        ];
    }

    ################################
    # TESTS
    ################################

    private Router $router;
    private $diContainerMock;
    private $authMock;
    private $renderServiceMock;
    private $homeControllerMock;
    private $userControllerMock;

    public function setUp(): void
    {
        # Auth
        $this->authMock = $this->createMock(Auth::class);
        # Container DIC
        $this->diContainerMock = $this->createMock(DIContainer::class);
        $this->diContainerMock->method('getAuth')->willReturn($this->authMock);
        # Render
        $this->renderServiceMock = $this->createMock(RenderService::class);
        # quelques controllers pour les tests
        $this->homeControllerMock = $this->createMock(HomeController::class);
        $this->userControllerMock = $this->createMock(UserController::class);

        # objet test
        $this->router = new Router(
            [
                ['GET', '/', 'HomeController', 'index'],
                ['GET', '/unknown-controller', 'UnknownController', 'index'],
                ['GET', '/unknown-middleware', 'HomeController', 'index', ['unknownMiddleware']],
                ['GET', '/require-role', 'UserController', 'loginAdmin', ['requireAdmin']],
                ['POST', '/require-login', 'UserController', 'logout', ['requireLogin']],
                ['POST', '/require-post-csrf', 'UserController', 'loginClient', ['requirePost&Csrf']],
            ], 
            $this->diContainerMock, $this->renderServiceMock, 'dev'
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDispatchSuccess()
    {
        $this->homeControllerMock->expects($this->once())->method('index')->willReturn(new Response('Content', 200, ['Content-Type' => 'text/html']));
        $this->diContainerMock->expects($this->once())->method('getHomeController')->willReturn($this->homeControllerMock);

        /** @var Response $response */
        $response = $this->router->dispatch('GET', '/');

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatus());
        $this->assertArrayHasKey('Content-Type', $response->getHeaders());
        $this->assertEquals('text/html', $response->getHeaders()['Content-Type']);
        $this->assertEquals('Content', $response->getContent());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDispatchControllerNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->router->dispatch('GET', '/unknown-controller');
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMiddlewareSuccessWithRequireLogin()
    {
        $this->authMock->expects($this->once())->method('requireLogin');
        $this->userControllerMock->expects($this->once())->method('logout');
        $this->diContainerMock->method('getUserController')->willReturn($this->userControllerMock);

        $this->router->dispatch('POST', '/require-login');
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMiddlewareSuccessWithRequireRole()
    {
        $this->authMock->expects($this->once())->method('requireRole');
        $this->userControllerMock->expects($this->once())->method('loginAdmin');
        $this->diContainerMock->method('getUserController')->willReturn($this->userControllerMock);

        $this->router->dispatch('GET', '/require-role');
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMiddlewareSuccessWithRequirePostAndCsrf()
    {
        $this->userControllerMock->expects($this->once())->method('loginClient');
        $this->diContainerMock->method('getUserController')->willReturn($this->userControllerMock);

        $_SERVER['REQUEST_METHOD'] = 'POST'; 
        $_SERVER['REQUEST_URI'] = '/require-post-csrf';
        $_POST['csrf_token'] = '123abc';
        $_SESSION['csrf_token'] = '123abc';

        $this->router->dispatch('POST', '/require-post-csrf');
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUnknownMiddlewareThrowsException()
    {
        $this->renderServiceMock->expects($this->once())
                ->method('render')
                ->with('500', [], 'error');

        $this->router->dispatch('GET', '/unknown-middleware');
    }
}