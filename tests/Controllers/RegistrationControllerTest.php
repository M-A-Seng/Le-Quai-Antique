<?php

namespace App\Tests\Controllers;

use App\Controllers\RegistrationController;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;
use App\Services\UserService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class RegistrationControllerTest extends TestCase
{
    private $userServiceMock;
    private $authMock;
    private $renderServiceMock;
    private $loggerMock;
    private RegistrationController $obj;
    public function setUp(): void
    {
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->renderServiceMock = $this->createMock(RenderService::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->obj = new RegistrationController($this->userServiceMock, $this->authMock, $this->renderServiceMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRegisterSuccess()
    {
        $_POST['email'] = 'test@gmail.com';
        $_POST['password'] = 'PASSword123*!';
        $_POST['csrf_token'] = '123abc';
        $this->userServiceMock->expects($this->once())->method('signUserUp');
        $this->userServiceMock->expects($this->once())->method('authenticateUser');
        $this->authMock->expects($this->once())->method('login');
        $response = $this->obj->register();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCheckEmail()
    {
        $this->userServiceMock->expects($this->once())->method('emailCheck');
        $response = $this->obj->checkEmail();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}