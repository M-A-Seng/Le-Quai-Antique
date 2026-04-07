<?php

namespace App\Tests\Config;

use App\Config\DbConnection;
use App\Core\Logger;
use App\Core\PdoFactory;
use App\Exceptions\DbFailureException;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DbConnectionTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function userTypeProvider(): array {
        return [
            'front' => ['front'],
            'back' => ['back'],
            'logs' => ['logs'],
        ];
    }

    ################################
    # DATA PROVIDERS
    ################################

    private $loggerMock;
    private $pdoFactoryMock;
    public function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->pdoFactoryMock = $this->createMock(PdoFactory::class);
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'DatabaseName';
        $_ENV['DB_USER_FRONT'] = 'front';
        $_ENV['DB_PASS_FRONT'] = 'front';
        $_ENV['DB_USER_BACK'] = 'back';
        $_ENV['DB_PASS_BACK'] = 'back';
        $_ENV['DB_USER_LOGS'] = 'logs';
        $_ENV['DB_PASS_LOGS'] = 'logs';
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMissingHostException(): void
    {
        unset($_ENV['DB_HOST']);
        $this->expectException(DbFailureException::class);
        $this->expectExceptionMessage("DB Host manquant");
        new DbConnection('front', $this->pdoFactoryMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMissingDbNameException(): void
    {
        unset($_ENV['DB_NAME']);
        $this->expectException(DbFailureException::class);
        $this->expectExceptionMessage("DB Name manquant");
        new DbConnection('front', $this->pdoFactoryMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInvalidUserTypeException(): void
    {
        $this->expectException(DbFailureException::class);
        $this->expectExceptionMessage("Utilisateur DB non valide");
        new DbConnection('unknown user', $this->pdoFactoryMock, $this->loggerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDbConnectionFailAndThrowsException(): void
    {
        $this->pdoFactoryMock->expects($this->once())->method('create')->willThrowException(new PDOException('fail'));
        $this->loggerMock->expects($this->once())->method('dbError')->with($this->stringContains('fail'));
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Erreur de connexion à la base de données.");
        new DbConnection('front', $this->pdoFactoryMock, $this->loggerMock);
    }

    #[DataProvider('userTypeProvider'), AllowMockObjectsWithoutExpectations]
    public function testDbConnectionSuccess(string $userType): void
    {
        $this->pdoFactoryMock->expects($this->once())->method('create');
        $dbConnection = new DbConnection($userType, $this->pdoFactoryMock, $this->loggerMock);
        $connection = $dbConnection->getConnection();
        $this->assertNotNull($connection);
        $this->assertInstanceOf(PDO::class, $connection);
    }
}