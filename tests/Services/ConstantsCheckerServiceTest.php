<?php

namespace App\Tests\Services;

use App\Exceptions\DataProcessingException;
use App\Services\ConstantsCheckerService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ConstantsCheckerServiceTest extends TestCase
{
    private ConstantsCheckerService $obj;

    # Constantes de test
    const VALID_CONST1 = false;
    const VALID_CONST2 = 12345;
    const VALID_CONST3 = 12.345;
    const VALID_CONST4 = 'Chaîne de caractères';
    const VALID_CONST5 = ['tableau' => 'associatif'];
    const VALID_CONST6 = [1, 3, 4, 5];

    const INVALID_CONST1 = null;
    const INVALID_CONST2 = '';
    const INVALID_CONST3 = ' ';
    const INVALID_CONST4 = [];
    const INVALID_CONST5 = [''];
    const INVALID_CONST6 = [' '];
    const INVALID_CONST7 = [null];
    const INVALID_CONST8 = '0';

    public function setUp(): void
    {
        $this->obj = new ConstantsCheckerService();
    }

    ################################
    # DATA PROVIDERS
    ################################

    public static function validConstantsToCheckProvider(): array {
        return [
            'one key array with string value, bool' => [['VALID_CONST1' => 'bool']],
            'one key array with string value, string' => [['VALID_CONST4' => 'string']],
            'one keys with array value, int + numeric' => [['VALID_CONST2' => ['int', 'integer', 'numeric']]],
            'one keys with array value, float + numeric' => [['VALID_CONST3' => ['float', 'double', 'real', 'numeric']]],
            'one keys with array value, array + iterable + countable' => [['VALID_CONST5' => ['array', 'iterable', 'countable']]],
            'one keys with array value, list + iterable + countable' => [['VALID_CONST6' => ['list', 'iterable', 'countable']]],
            'one keys with array uppercase values' => [['VALID_CONST2' => ['INT', 'INTEGER', 'NUMERIC']]],
            'random several key array with random lettercase values' => [['VALID_CONST4' => 'stRing', 'VALID_CONST1' => 'BOOL', 'VALID_CONST2' => 'int', 'VALID_CONST5' => 'ArRAY']],
        ];
    }
    public static function invalidConstantsToCheckProvider(): array {
        return [
            'empty array' => [[]],
            'empty key' => [['' => 'string']],
            'empty value' => [['VALID_CONST1' => '']],

            'not string const' => [[true => 'string']],
            'not string value' => [['VALID_CONST1' => 123]],

            'numeric const' => [['123' => 'string']],
            'numeric value' => [['VALID_CONST1' => '123']],

            'const lowercase' => [['valid_const1' => 'bool']],
            'const partially lowercase' => [['VALID_Const1' => 'bool']],
            'const undefined' => [['MY_CONST' => 'string']],

            'not allowed type' => [['INVALID_CONST1' => 'null']],
            'invalid value' => [['VALID_CONST1' => 'unknown']],
            'invalid type' => [['VALID_CONST1' => 'float']],
            'type assoc array with type = type' => [['VALID_CONST1' => ['type' => 'bool']]],
            'type assoc array with type = value' => [['VALID_CONST1' => ['bool' => true]]],

            'allowed type but const defined null' => [['INVALID_CONST1' => 'string']],
            'allowed type but const defined empty' => [['INVALID_CONST2' => 'string']],
            'allowed type but const defined string with space only' => [['INVALID_CONST3' => 'string']],
            'allowed type but const defined empty array' => [['INVALID_CONST4' => 'array']],
            'allowed type but const defined array with empty string' => [['INVALID_CONST5' => 'array']],
            'allowed type but const defined array with string & only space' => [['INVALID_CONST6' => 'array']],
            'allowed type but const defined array with null value' => [['INVALID_CONST7' => 'array']],
            'allowed type but const defined string with 0' => [['INVALID_CONST8' => 'string']],
        ];
    }

    ################################
    # TESTS
    ################################

    #[DataProvider('validConstantsToCheckProvider')]
    public function testValidateConstantsSuccess(array $constantsToCheck): void
    {
        $this->obj->validateConstants($constantsToCheck, static::class);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidConstantsToCheckProvider')]
    public function testValidateConstantsThrowsException(array $constantsToCheck): void
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->validateConstants($constantsToCheck, static::class);
    }
}