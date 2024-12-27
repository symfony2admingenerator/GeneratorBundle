<?php

namespace Admingenerator\GeneratorBundle\Tests\Mocks\Doctrine;

use Doctrine\DBAL\Driver\Result;
use Override;

/**
 * @method string getColumnName(int $index)
 */
class ResultMock implements Result
{
    #[Override]
    public function fetchNumeric(): array|false
    {
        return false;
    }

    #[Override]
    public function fetchAssociative(): array|false
    {
        return false;
    }

    #[Override]
    public function fetchOne(): mixed
    {
        return null;
    }

    #[Override]
    public function fetchAllNumeric(): array
    {
        return [];
    }

    #[Override]
    public function fetchAllAssociative(): array
    {
        return [];
    }

    #[Override]
    public function fetchFirstColumn(): array
    {
        return [];
    }

    #[Override]
    public function rowCount(): int|string
    {
        return 0;
    }

    #[Override]
    public function columnCount(): int
    {
        return 0;
    }

    #[Override]
    public function free(): void
    {
    }

    #[Override]
    public function __call(string $name, array $arguments)
    {
    }
}