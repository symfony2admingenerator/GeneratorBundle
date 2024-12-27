<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Override;

class ConnectionMock extends Connection
{
    private DatabasePlatformMock $_platformMock;
    private int $_lastInsertId = 0;
    private array $_inserts = [];
    private array $_executeUpdates = [];
    private DatabasePlatformMock $_platform;

    public function __construct(array $params, $driver, $config = null)
    {
        $this->_platformMock = new DatabasePlatformMock();

        parent::__construct($params, $driver, $config);

        // Override possible assignment of platform to database platform mock
        $this->_platform = $this->_platformMock;
    }

    #[Override]
    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->_platformMock;
    }

    #[Override]
    public function insert($table, array $data, array $types = array()): int
    {
        $this->_inserts[$table][] = $data;
        return $this->_lastInsertId++;
    }


    #[Override]
    public function lastInsertId($seqName = null): int
    {
        return $this->_lastInsertId;
    }

    #[Override]
    public function quote($value, $type = null): string
    {
        if (is_string($value)) {
            return "'" . $value . "'";
        }

        return $value;
    }

    /* Mock API */

    public function setDatabasePlatform($platform): void
    {
        $this->_platformMock = $platform;
    }

    public function setLastInsertId($id): void
    {
        $this->_lastInsertId = $id;
    }

    public function getInserts(): array
    {
        return $this->_inserts;
    }

    public function getExecuteUpdates(): array
    {
        return $this->_executeUpdates;
    }

    public function reset(): void
    {
        $this->_inserts = [];
        $this->_lastInsertId = 0;
    }
}
