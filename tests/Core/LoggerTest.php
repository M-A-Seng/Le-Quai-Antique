<?php

namespace App\Tests\Core;

use App\Core\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private string $logFile;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logFile = __DIR__ . '\..\..\logs\test.log';
        $this->logger = new Logger('\..\..\logs\test.log');
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testInfoLogsMessage(): void
    {
        $this->logger->info('Message INFO');
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('[INFO]', $content);
        $this->assertStringContainsString('Message INFO', $content);
    }

    public function testWarningLogsMessage(): void
    {
        $this->logger->warning('Message WARNING');
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('[WARNING]', $content);
        $this->assertStringContainsString('Message WARNING', $content);
    }

    public function testErrorLogsMessage(): void
    {
        $this->logger->error('Message ERROR');
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('[ERROR]', $content);
        $this->assertStringContainsString('Message ERROR', $content);
    }

    public function testDbErrorLogsMessage(): void
    {
        $this->logger->dbError('Message DB ERROR');
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('[DATABASE ERROR]', $content);
        $this->assertStringContainsString('Message DB ERROR', $content);
    }
}