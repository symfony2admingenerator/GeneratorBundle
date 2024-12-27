<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DateIntervalUnit;
use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\TransactionIsolationLevel;
use Override;

class DatabasePlatformMock extends AbstractPlatform
{
    private string $_sequenceNextValSql = "";

    #[Override]
    public function getSequenceNextValSQL($sequence): string
    {
        return $this->_sequenceNextValSql;
    }

    #[Override]
    public function getBooleanTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getIntegerTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getBigIntTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getSmallIntTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    protected function _getCommonIntegerTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getClobTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    /* MOCK API */

    public function getName(): string
    {
        return 'mock';
    }

    #[Override]
    protected function initializeDoctrineTypeMappings(): void
    {

    }

    #[Override]
    public function getBlobTypeDeclarationSQL(array $column): string
    {
        throw NotSupported::new(__METHOD__);
    }

    #[Override]
    public function getLocateExpression(string $string, string $substring, ?string $start = null): string
    {
        return '';
    }

    #[Override]
    public function getDateDiffExpression(string $date1, string $date2): string
    {
        return '';
    }

    #[Override]
    protected function getDateArithmeticIntervalExpression(string $date, string $operator, string $interval, DateIntervalUnit $unit,): string
    {
        return '';
    }

    #[Override]
    public function getCurrentDatabaseExpression(): string
    {
        return '';
    }

    #[Override]
    public function getAlterTableSQL(TableDiff $diff): array
    {
        return [];
    }

    #[Override]
    public function getListViewsSQL(string $database): string
    {
        return '';
    }

    #[Override]
    public function getSetTransactionIsolationSQL(TransactionIsolationLevel $level): string
    {
        return '';
    }

    #[Override]
    public function getDateTimeTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getDateTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    public function getTimeTypeDeclarationSQL(array $column): string
    {
        return '';
    }

    #[Override]
    protected function createReservedKeywordsList(): KeywordList
    {
        return new class extends KeywordList {
            protected function getKeywords(): array
            {
                return [];
            }
        };
    }

    #[Override]
    public function createSchemaManager(Connection $connection): AbstractSchemaManager
    {
        return $connection->createSchemaManager();
    }
}
