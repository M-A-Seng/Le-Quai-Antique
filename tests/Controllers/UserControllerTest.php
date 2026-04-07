<?php

namespace App\Tests\Controllers;

use App\Controllers\UserController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private $renderServiceMock;
    private $authMock;
    private $loggerMock;
    private UserController $obj;
    public function setUp(): void
    {
        $this->renderServiceMock = $this->createMock(RenderService::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->obj = new UserController($this->authMock, $this->renderServiceMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoginClientSuccess()
    {
        $this->renderServiceMock->expects($this->once())->method('render')->with("profile");
        $response = $this->obj->loginClient();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLoginAdminSuccess()
    {
        $this->renderServiceMock->expects($this->once())->method('render')->with("admin");
        $response = $this->obj->loginAdmin();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testLogoutSuccess()
    {
        $this->authMock->expects($this->once())->method('logout');
        $response = $this->obj->logout();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}