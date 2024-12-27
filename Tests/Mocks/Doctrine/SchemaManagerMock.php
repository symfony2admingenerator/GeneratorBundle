<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Composer\Util\Platform;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Override;

class SchemaManagerMock extends AbstractSchemaManager
{
    public function __construct(Connection $conn, Platform $platform)
    {
        parent::__construct($conn, $platform);
    }

    #[Override]
    protected function _getPortableTableColumnDefinition($tableColumn): Column
    {
        return new Column('', Type::getType(Types::STRING));
    }

    #[Override]
    protected function selectTableNames(string $databaseName): Result
    {
        return new Result(new ResultMock(), new ConnectionMock([], new DriverMock()));
    }

    #[Override]
    protected function selectTableColumns(string $databaseName, ?string $tableName = null): Result
    {
        return new Result(new ResultMock(), new ConnectionMock([], new DriverMock()));
    }

    #[Override]
    protected function selectIndexColumns(string $databaseName, ?string $tableName = null): Result
    {
        return new Result(new ResultMock(), new ConnectionMock([], new DriverMock()));
    }

    #[Override]
    protected function selectForeignKeyColumns(string $databaseName, ?string $tableName = null): Result
    {
        return new Result(new ResultMock(), new ConnectionMock([], new DriverMock()));
    }

    #[Override]
    protected function fetchTableOptionsByTable(string $databaseName, ?string $tableName = null): array
    {
        return [];
    }

    #[Override]
    protected function _getPortableTableDefinition(array $table): string
    {
        return '';
    }

    #[Override]
    protected function _getPortableViewDefinition(array $view): View
    {
        return new View('', '');
    }

    #[Override]
    protected function _getPortableTableForeignKeyDefinition(array $tableForeignKey): ForeignKeyConstraint
    {
        return new ForeignKeyConstraint([], '', []);
    }
}
