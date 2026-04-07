<?php

namespace App\Tests\Services;

use App\Exceptions\InvalidFieldException;
use App\Models\RestaurantModel;
use App\Services\RestaurantService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RestaurantServiceTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validDataForUpdateRestaurantProvider(): array {
        return [
            'one key value' => [["evening_max_guests" => "150"]],
            'lunch only' => [["lunch_opening_time" => "12:00", "lunch_closing_time" => "14:00", "lunch_max_guests" => "100"]],
            'evening only' => [["evening_opening_time" => "20:00", "evening_closing_time" => "22:00", "evening_max_guests" => "150"]],
            'lunch and evening' => [["lunch_opening_time" => "12:00", "lunch_closing_time" => "14:00", "lunch_max_guests" => "100",
                                    "evening_opening_time" => "20:00", "evening_closing_time" => "22:00", "evening_max_guests" => "150"]],
        ];
    }
    public static function invalidDataForUpdateRestaurantProvider(): array {
        return [
            'list array as value' => [["lunch_opening_time" => ["12:00"]]],
            'assoc array as value' => [["lunch_opening_time" => ["time" => "12:00"]]],
            'invalid key' => [["lunch opening time" => "12:00"]],
            'unknown key' => [["unknown" => "12:00"]],
        ];
    }

    ################################
    # TESTS
    ################################

    private RestaurantService $obj;
    private $restaurantModelMock;
    public function setUp(): void
    {
        $this->restaurantModelMock = $this->createMock(RestaurantModel::class);
        $this->obj = new RestaurantService($this->restaurantModelMock);
        $_SESSION['id'] = 1;
    }

    public function testGetRestaurantSuccess()
    {
        $this->restaurantModelMock->expects($this->once())
            ->method('getRestaurantByAdmin')
            ->with(1)
            ->willReturn(['name' => 'restaurant']);
        
        $result = $this->obj->getRestaurant();
        $this->assertEquals(['name' => 'restaurant'], $result);
    }

    public function testGetRestaurantServicesSuccess()
    {
        $dbReturn = [
            'lunch_opening_time' => '12:00:00.000000',
            'lunch_closing_time' => '14:30:00.000000',
            'evening_opening_time' => '19:15:00.000000',
            'evening_closing_time' => '22:45:00.000000',
            "lunch_max_guests" => '100',
            "evening_max_guests" => '150',
        ];
        $expected = [
            'lunchOpeningTime' => '12:00',
            'lunchClosingTime' => '14:30',
            'eveningOpeningTime' => '19:15',
            'eveningClosingTime' => '22:45',
            "lunchMaxGuests" => '100',
            "eveningMaxGuests" => '150',
        ];
        $this->restaurantModelMock->expects($this->once())
            ->method('getRestaurantByAdmin')
            ->with(1)
            ->willReturn($dbReturn);
        
        $result = $this->obj->getRestaurantServices();
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('validDataForUpdateRestaurantProvider')]
    public function testUpdateRestaurantServicesSuccess(array $data)
    {
        $this->restaurantModelMock->expects($this->once())
            ->method('updateRestaurant')
            ->with($data);
        
        $this->obj->updateRestaurantServices($data);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidDataForUpdateRestaurantProvider'), AllowMockObjectsWithoutExpectations]
    public function testUpdateRestaurantServicesThrowsException(array $data)
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->updateRestaurantServices($data);
    }
}