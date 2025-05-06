<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use Override;

class DriverMock implements Driver
{
    private ?DatabasePlatformMock $_platformMock = null;

    public function connect(array $params, $username = null, $password = null, array $driverOptions = []): Driver\Connection
    {
        return new DriverConnectionMock();
    }

    #[Override]
    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
    {
        if (!$this->_platformMock) {
            $this->_platformMock = new DatabasePlatformMock;
        }

        return $this->_platformMock;
    }

    /* MOCK API */
    public function getName()
    {
        return 'mock';
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return new ExceptionConverterMock();
    }
}
