<?php

namespace App\Tests\Core\Abstract;

use App\Core\Abstract\AbstractDataProcessingService;
use App\Exceptions\DataProcessingException;
use App\Exceptions\InvalidArrayForDbException;
use App\Exceptions\InvalidFieldException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

################################
# TESTABLE CLASS
################################
class TestableAbstractDataProcessingService extends AbstractDataProcessingService 
{
    protected const NOT_NULL_COLUMNS = ['name', 'email'];
    # Accéder aux méthodes protected
    public function validateNotNullKeysMethod(string $className, array $data, bool $checkAllRequiredKeys = false) {
        return $this->validateNotNullKeys($className, $data, $checkAllRequiredKeys);
    }
    public function validateTimeFormatMethod(string $stringTime) {
        return $this->validateTimeFormat($stringTime);
    }
    public function validateTimeIntervalMethod(string $startTime, string $endTime, int $minutesInterval) {
        return $this->validateTimeInterval($startTime, $endTime, $minutesInterval);
    }
    public function formatTimeToHHMMMethod(?string $time) {
        return $this->formatTimeToHHMM($time);
    }
    public function validatePositiveIntegerMethod(string $stringInteger) {
        return $this->validatePositiveInteger($stringInteger);
    }
}

# ---

class AbstractDataProcessingServiceTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function invalidNotNullKeysProvider(): array {
        return [
            'clé manquante (strict)' => [
                ['email' => 'test@test.com'], true
            ],
            'clé vide (strict)' => [
                ['name' => '', 'email' => 'test@test.com'], true
            ],
            'clé null (non strict)' => [
                ['name' => null], false
            ],
        ];
    }
    public static function validNotNullKeysProvider(): array {
        return [
            'toutes les clés valides (strict)' => [
                ['name' => 'John', 'email' => 'test@test.com'], true
            ],
            'clé absente autorisée (non strict)' => [
                ['email' => 'test@test.com'], false
            ],
        ];
    }
    public static function validTimeFormatsProvider(): array {
        return [
            'heure avec minutes' => ['15:49'],
            'heure pile' => ['13:00'],
            'nuit' => ['04:20'],
        ];
    }
    public static function invalidTimeFormatsProvider(): array {
        return [
            '"h" à la place de ":"' => ['15h49'],
            'chiffre en trop' => ['13:005'],
            'chiffre en moins' => ['4:20'],
            'avec secondes' => ['10:27:56'],
            'heure sans minutes' => ['17'],
        ];
    }
    public static function validTimeIntervalsProvider(): array {
        return [
            'intervalle simple' => ['10:00', '10:30', 30],
            'passage minuit' => ['23:30', '00:00', 30],
            'après minuit' => ['23:30', '01:30', 120]
        ];
    }
    public static function invalidTimeIntervalsProvider(): array {
        return [
            'intervalle incorrect' => ['10:00', '10:20', 30],
            'mauvais intervalle nuit' => ['23:00', '00:00', 30],
        ];
    }
    public static function validTimesProvider(): array {
        return [
            'format H:i:s' => ['14:30:00', '14:30'],
            'format H:i' => ['14:30', '14:30'],
            'datetime complet' => ['2024-01-01 14:30:00', '14:30'],
            'format custom' => ['14h30', '14:30'],
        ];
    }
    public static function invalidTimesProvider(): array {
        return [
            'vide' => [''],
            'format invalide' => ['invalid'],
        ];
    }
    public static function invalidPositiveIntegersProvider(): array {
        return [
            'zéro' => ['0'],
            'négatif' => ['-5'],
            'lettres' => ['abc'],
            'float' => ['12.5'],
        ];
    }
    public static function validPositiveIntegersProvider(): array {
        return [
            'entier valide' => ['5'],
            'grand entier' => ['123456'],
        ];
    }

    ################################
    # TESTS
    ################################

    private TestableAbstractDataProcessingService $obj;
    protected function setUp(): void {
        $this->obj = new TestableAbstractDataProcessingService();
    }

    #[DataProvider('validNotNullKeysProvider')]
    public function testValidKeysInArray(array $data, bool $strict): void
    {
        $this->obj->validateNotNullKeysMethod(TestableAbstractDataProcessingService::class, $data, $strict);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidNotNullKeysProvider')]
    public function testThrowExceptionForInvalidKeysInArray(array $data, bool $strict): void
    {
        $this->expectException(InvalidArrayForDbException::class);
        $this->obj->validateNotNullKeysMethod(TestableAbstractDataProcessingService::class, $data, $strict);
    }

    #[DataProvider('validTimeFormatsProvider')]
    public function testValidTimeFormat(string $stringTime): void
    {
        $this->obj->validateTimeFormatMethod($stringTime);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidTimeFormatsProvider')]
    public function testThrowExceptionForInvalidTimeFormat(string $stringTime): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->validateTimeFormatMethod($stringTime);
    }

    #[DataProvider('validTimeIntervalsProvider')]
    public function testValidTimeInterval(string $startTime, string $endTime, int $minutesInterval): void
    {
        $this->obj->validateTimeIntervalMethod($startTime, $endTime, $minutesInterval);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidTimeIntervalsProvider')]
    public function testThrowExceptionForInvalidTimeInterval(string $startTime, string $endTime, int $minutesInterval): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->validateTimeIntervalMethod($startTime, $endTime, $minutesInterval);
    }

    #[DataProvider('validTimesProvider')]
    public function testSuccessFormatTimeToHHMM(?string $time, string $expected): void
    {
        $result = $this->obj->formatTimeToHHMMMethod($time);
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('invalidTimesProvider')]
    public function testThrowExceptionForFailedFormatTimeToHHMM(?string $time): void
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->formatTimeToHHMMMethod($time);
    }

    #[DataProvider('validPositiveIntegersProvider')]
    public function testValidPositivInteger(string $stringInteger): void
    {
        $this->obj->validatePositiveIntegerMethod($stringInteger);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidPositiveIntegersProvider')]
    public function testThrowExceptionForInvalidPositivInteger(string $stringInteger): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->validatePositiveIntegerMethod($stringInteger);
    }

}