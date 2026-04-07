<?php

namespace App\Tests\Core\Abstract;

use App\Config\DbConnection;
use App\Core\Abstract\AbstractModel;
use App\Exceptions\DataProcessingException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TestableAbstractModel extends AbstractModel
{
    protected const ALLOWED_TABLES=["users"];
    protected const ALLOWED_COLUMNS=["id", "name", "age", "data"];
    protected const TABLE="users";
    public function __construct(DbConnection $connection) {
        parent::__construct($connection);
    }
    # Accéder aux méthodes protected
    public function insertMethod(array $data): int {
        return $this->insert($data);
    }
    public function findAllMethod(): array {
        return $this->findAll();
    }
    public function findByMethod(string $column, mixed $value): array {
        return $this->findBy($column, $value);
    }
    public function updateMethod(int $id, array $data): int {
        return $this->update($id, $data);
    }
    public function deleteMethod(array $conditions): int {
        return $this->delete($conditions);
    }
}

class AbstractModelTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function emptyArgumentsForFindByProvider(): array {
        return [
            'first empty' => ['', 1],
            'second empty' => ['id', ''],
            'both empty' => ['', ''],
        ];
    }
    public static function emptyArgumentsForUpdateProvider(): array {
        return [
            'first empty' => [0, ['data' => 'data']],
            'second empty' => [1, []],
            'both empty' => [0, []],
        ];
    }

    ################################
    # TESTS
    ################################

    private TestableAbstractModel $obj;
    private $dbMock;
    private $pdoMock;
    private $stmtMock;
    public function setUp(): void
    {
        # stmt
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([0 => 'data']);
        $this->stmtMock->method('fetchColumn')->willReturn(1);
        $this->stmtMock->method('rowCount')->willReturn(2);
        # pdo
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        # db
        $this->dbMock = $this->createMock(DbConnection::class);
        $this->dbMock->method('getConnection')->willReturn($this->pdoMock);
        # Objet test
        $this->obj = new TestableAbstractModel($this->dbMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testInsertSuccess()
    {
        $data = ['name' => 'Alice', 'age' => 30];
        $this->stmtMock->expects($this->once())->method('execute')
        ->with($this->callback(fn($params) => $params === $data));

        $result = $this->obj->insertMethod($data);
        $this->assertEquals(1, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testThrowExceptionForEmptyArgumentInInsert()
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->insertMethod([]);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindAllSuccess()
    {
        $this->stmtMock->expects($this->once())->method('execute');

        $result = $this->obj->findAllMethod();
        $this->assertEquals([0 => 'data'], $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFindBySuccess()
    {
        $this->stmtMock->expects($this->once())->method('execute')
            ->with($this->callback(fn($params) => $params['value'] === 1));

        $result = $this->obj->findByMethod('id', 1);
        $this->assertEquals([0 => 'data'], $result);
    }

    #[DataProvider('emptyArgumentsForFindByProvider'), AllowMockObjectsWithoutExpectations]
    public function testThrowExceptionForEmptyArgumentInFindBy(string $column, mixed $value)
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->findByMethod($column, $value);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateSuccess()
    {
        $this->stmtMock->expects($this->once())->method('execute')
        ->with($this->callback(fn($params) => $params['id'] === 1 && $params['data'] === 'data'));

        $result = $this->obj->updateMethod(1, ['data' => 'data']);
        $this->assertEquals(2, $result);
    }

    #[DataProvider('emptyArgumentsForUpdateProvider'), AllowMockObjectsWithoutExpectations]
    public function testThrowExceptionForEmptyArgumentInUpdate(int $id, array $data)
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->updateMethod($id, $data);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDeleteSuccess()
    {
        $this->stmtMock->expects($this->once())->method('execute');

        $result = $this->obj->deleteMethod(['id' => 1]);
        $this->assertEquals(2, $result);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testThrowExceptionForEmptyArgumentInDelete()
    {
        $this->expectException(DataProcessingException::class);
        $this->obj->deleteMethod([]);
    }
}
