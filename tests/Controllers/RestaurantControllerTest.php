<?php

namespace App\Tests\Controllers;

use App\Controllers\RestaurantController;
use App\Core\Logger;
use App\Core\Response;
use App\Services\RenderService;
use App\Services\RestaurantService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RestaurantControllerTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validIndexDataProvider(): array {
        return [
            'no parametters' => [],
            'with extra data' => [['extra' => 'data']],
            'with different http' => [[], 404],
            'with extra data + different http' => [['extra' => 'data', 500]],
        ];
    }

    ################################
    # TESTS
    ################################

    private $restaurantServiceMock;
    private $renderServiceMock;
    private $loggerMock;
    private RestaurantController $obj;
    public function setUp(): void
    {
        $this->restaurantServiceMock = $this->createMock(RestaurantService::class);
        $this->renderServiceMock = $this->createMock(RenderService::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->obj = new RestaurantController($this->restaurantServiceMock, $this->renderServiceMock, $this->loggerMock);
    }

    #[DataProvider('validIndexDataProvider'), AllowMockObjectsWithoutExpectations]
    public function testIndexSuccess(array $extraData = [], int $http = 200)
    {
        $this->restaurantServiceMock->expects($this->once())->method('getRestaurantServices')->willReturn(['service' => 'service']);
        $response = $this->obj->index($extraData, $http);
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateRestaurantSuccess()
    {
        $this->restaurantServiceMock->expects($this->once())->method('updateRestaurantServices');
        $this->restaurantServiceMock->expects($this->once())->method('getRestaurantServices')->willReturn(['service' => 'service']);
        $response = $this->obj->updateRestaurant();
        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }
}