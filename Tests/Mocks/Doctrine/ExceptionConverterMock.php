<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Query;
use Override;

class ExceptionConverterMock implements ExceptionConverter
{
    #[Override]
    public function convert(Exception $exception, ?Query $query): DriverException
    {
        return new DriverException($exception, $query);
    }
}