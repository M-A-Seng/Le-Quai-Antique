<?php

namespace App\Tests\Core\Abstract;

use App\Core\Abstract\AbstractCheckersModel;
use App\Exceptions\InvalidArrayForDbException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

################################
# TESTABLE CLASS
################################
class TestableAbstractCheckersModel extends AbstractCheckersModel
{
    protected const ALLOWED_TABLES = ['users', 'posts'];
    protected const ALLOWED_COLUMNS = ['id', 'name', 'email'];
    # Accéder aux méthodes protected
    public function filterAllowedTablesMethod(string $className, array|string $table): array|string {
        return $this->filterAllowedTables($className, $table);
    }
    public function filterAllowedColumnsMethod(string $className, array|string $data): array|string {
        return $this->filterAllowedColumns($className, $data);
    }
    public function checkProtectedColumnsMethod(array $data, array $protectedColumns) {
        return $this->checkProtectedColumns($data, $protectedColumns);
    }
}

# ---

class AbstractCheckersModelTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validTablesProvider(): array {
        return [
            'single valid table' => ['users'],
            'single valid table uppercase' => ['USERS'],
            'multiple valid tables' => [['users', 'posts']],
            'mixed case tables' => [['Users', 'POSTS']],
        ];
    }
    public static function invalidTablesProvider(): array {
        return [
            'single invalid table' => ['comments'],
            'mixed valid + invalid' => [['users', 'comments']],
            'multiple invalid tables' => [['foo', 'bar']],
            'empty array' => [[]],
            'empty string' => [''],
        ];
    }
    public static function validColumnsProvider(): array {
        return [
            'single column string' => ['id'],
            'array of columns' => [['id', 'name']],
            'assoc array (keys used)' => [['id' => 1, 'name' => 'John']],
            'mixed case columns' => [['ID', 'Name']],
            'numeric keys array' => [[0 => 'id', 1 => 'name']],
        ];
    }
    public static function invalidColumnsProvider(): array {
        return [
            'single invalid column' => ['password'],
            'mixed valid + invalid' => [['id', 'password']],
            'assoc array with invalid key' => [['id' => 1, 'password' => 'xxx']],
            'empty array' => [[]],
            'empty string' => [''],
            'mixed assoc + invalid key but valid value' => [['id' => 1, 'name' => 'John', 'email' => 'x', 'foo' => 'bar']]
        ];
    }
    public static function protectedColumnsProvider(): array {
        return [
            'one protected column' => [
                ['id' => 1, 'name' => 'John'],
                ['id']
            ],
            'multiple protected columns' => [
                ['id' => 1, 'email' => 'test@test.com'],
                ['id', 'email']
            ],
        ];
    }
    public static function noProtectedColumnsProvider(): array {
        return [
            'no protected columns hit' => [
                ['name' => 'John'],
                ['id']
            ],
            'empty protected list' => [
                ['id' => 1],
                []
            ],
        ];
    }

    ################################
    # TESTS
    ################################

    private TestableAbstractCheckersModel $obj;
    protected function setUp(): void {
        $this->obj = new TestableAbstractCheckersModel;
    }

    #[DataProvider('validTablesProvider')]
    public function testAllowedTablesInClass(array|string $table): void
    {
        $result = $this->obj->filterAllowedTablesMethod(TestableAbstractCheckersModel::class, $table);
        $this->assertEquals($table, $result);
    }

    #[DataProvider('invalidTablesProvider')]
    public function testInvalidTablesInClass(array|string $table): void
    {
        $this->expectException(InvalidArrayForDbException::class);
        $this->obj->filterAllowedTablesMethod(TestableAbstractCheckersModel::class, $table);
    }

    #[DataProvider('validColumnsProvider')]
    public function testAllowedColumnsInClass(array|string $table): void
    {
        $result = $this->obj->filterAllowedColumnsMethod(TestableAbstractCheckersModel::class, $table);
        $this->assertEquals($table, $result);
    }

    #[DataProvider('invalidColumnsProvider')]
    public function testInvalidColumnsInClass(array|string $table): void
    {
        $this->expectException(InvalidArrayForDbException::class);
        $this->obj->filterAllowedColumnsMethod(TestableAbstractCheckersModel::class, $table);
    }

    #[DataProvider('noProtectedColumnsProvider')]
    public function testNoProtectedColumnsInArray(array $data, array $protectedColumns): void
    {
        $this->obj->checkProtectedColumnsMethod($data, $protectedColumns);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('protectedColumnsProvider')]
    public function testThrowExceptionForProtectedColumnsInArray(array $data, array $protectedColumns): void
    {
        $this->expectException(InvalidArrayForDbException::class);
        $this->obj->checkProtectedColumnsMethod($data, $protectedColumns);
    }
}