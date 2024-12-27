<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use Override;

class StatementMock implements Statement
{
    #[Override]
    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
    }

    #[Override]
    public function execute(): Result
    {
        return new ResultMock();
    }
}