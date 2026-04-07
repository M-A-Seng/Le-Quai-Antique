<?php

namespace App\Tests\Core;

use App\Core\Auth;
use App\Enums\Role;
use App\Exceptions\DataProcessingException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\RequireLoginException;
use App\Services\SessionService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validLoginDataProvider(): array {
        return [
            'new user default' => [['id' => 1, 'role' => 'CLIENT']],
            'new user false' => [['id' => 1, 'role' => 'ADMIN'], false],
            'new user true' => [['id' => 1, 'role' => 'CLIENT'], true],
        ];
    }
    public static function invalidLoginDataProvider(): array {
        return [
            'empty id' => [['id' => 0, 'role' => 'CLIENT']],
            'empty role' => [['id' => 1, 'role' => ''], false],
            'key missing' => [['role' => 'CLIENT'], true],
        ];
    }

    ################################
    # TESTS
    ################################

    private $sessionMock;
    private Auth $auth;
    public function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionService::class);
        $this->auth = new Auth($this->sessionMock);
    }

    #[DataProvider('validLoginDataProvider')]
    public function testLoginSuccess(array $userData, bool $newUser = false)
    {
        session_start();
        $this->sessionMock->expects($this->exactly(3))->method('set');
        $this->auth->login($userData, $newUser);
        session_destroy();
    }

    #[DataProvider('invalidLoginDataProvider'), AllowMockObjectsWithoutExpectations]
    public function testLoginThrowsException(array $userData, bool $newUser = false)
    {
        $this->expectException(DataProcessingException::class);
        $this->auth->login($userData, $newUser);
    }

    public function testCheckSuccess()
    {
        $this->sessionMock->expects($this->once())->method('has')->with('id')->willReturn(true);
        $this->assertTrue($this->auth->check());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRequireLoginThrowsException()
    {
        $this->expectException(RequireLoginException::class);
        $this->auth->requireLogin();
    }

    public function testRequireRoleSuccess()
    {
        $this->sessionMock->expects($this->once())->method('has')->with('id')->willReturn(true);
        $this->sessionMock->expects($this->once())->method('get')->with('role')->willReturn(Role::CLIENT);
        $this->auth->requireRole(Role::CLIENT);
    }

    public function testRequireRoleThrowsException()
    {
        $this->sessionMock->expects($this->once())->method('has')->with('id')->willReturn(true);
        $this->sessionMock->expects($this->once())->method('get')->with('role')->willReturn(Role::CLIENT);
        $this->expectException(ForbiddenException::class);
        $this->auth->requireRole(Role::ADMIN);
    }

    public function testLogoutSuccess()
    {
        $this->sessionMock->expects($this->once())->method('destroy');
        $this->auth->logout();
    }
}