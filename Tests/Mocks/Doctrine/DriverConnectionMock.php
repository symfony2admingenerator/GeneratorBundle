<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

class DriverConnectionMock implements Connection
{
    public function prepare($sql): Statement
    {
        return new StatementMock();
    }
    public function query(string $sql): Result
    {
        return new ResultMock();
    }
    public function quote($value, $type=\PDO::PARAM_STR): string
    {
        return '';
    }
    public function exec($sql): int|string
    {
        return '';
    }
    public function lastInsertId($name = null): int|string
    {
        return 0;
    }
    public function beginTransaction(): void
    {}
    public function commit(): void
    {}
    public function rollBack(): void
    {}
    public function errorCode() {}

    public function getNativeConnection(): void
    {}

    public function getServerVersion(): string
    {
        return '';
    }
}
