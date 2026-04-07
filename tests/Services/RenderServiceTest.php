<?php

namespace App\Tests\Services;

use App\Exceptions\NotFoundException;
use App\Services\RenderService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RenderServiceTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validViewsProvider(): array {
        return [
            'public view with 1 argument' => ['home'],
            'private view with 1 argument' => ['profile'],
            'public view with all arguments' => ['home', [], 'main'],
            'private view with all arguments' => ['profile', [], 'main'],
            'error view' => ['404', [], 'error'],
        ];
    }

    ################################
    # DATA PROVIDERS
    ################################

    private RenderService $obj;
    public function setUp(): void
    {
        $this->obj = new RenderService();
    }

    #[DataProvider('validViewsProvider')]
    public function testRenderSuccess(string $view, array $data = [], string $layout = "main")
    {
        $_SESSION['csrf_token'] = 'abc123';
        $_SESSION['new_user'] = false;
        $html = $this->obj->render($view, $data, $layout, true);
        $this->assertIsString($html);
    }

    public function testRenderExceptionForNotFoundView()
    {
        $this->expectException(NotFoundException::class);
        $this->obj->render('unknown view');
    }

    public function testRenderExceptionForNotFoundLayout()
    {
        $this->expectException(NotFoundException::class);
        $this->obj->render('home', [], 'unknown layout');
    }
}