<?php

namespace App\Tests\Controllers;

use App\Controllers\AuthenticationController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;
use App\Services\UserService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class AuthenticationControllerTest extends TestCase
{
    private $userServiceMock;
    private $authMock;
    private $renderServiceMock;
    private $loggerMock;
    private AuthenticationController $obj;
    public function setUp(): void
    {
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->renderServiceMock = $this->createMock(RenderService::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->obj = new AuthenticationController($this->userServiceMock, $this->authMock, $this->renderServiceMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAuthenticateSuccess(): void
    {
        $userData = [
            'id' => '1',
            'role' => 'CLIENT',
        ];
        $this->userServiceMock->expects($this->once())->method('authenticateUser')->willReturn($userData);
        $this->authMock->expects($this->once())->method('login')->with($userData);

        $response = $this->obj->authenticate();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAuthenticateCatchesExceptionForEmptyData(): void
    {
        $userData = [
            'id' => '0',
            'role' => '',
        ];
        $this->userServiceMock->expects($this->once())->method('authenticateUser')->willReturn($userData);
        $this->authMock->expects($this->never())->method('login')->with($userData);
        $this->renderServiceMock->expects($this->once())->method('render');
        $this->loggerMock->expects($this->once())->method('error');

        $response = $this->obj->authenticate();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAuthenticateCatchesExceptionForInvalidRole(): void
    {
        $userData = [
            'id' => '1',
            'role' => 'invalid role',
        ];
        $this->userServiceMock->expects($this->once())->method('authenticateUser')->willReturn($userData);
        $this->authMock->expects($this->never())->method('login')->with($userData);
        $this->renderServiceMock->expects($this->once())->method('render');
        $this->loggerMock->expects($this->once())->method('error');

        $response = $this->obj->authenticate();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}