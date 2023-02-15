<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

use DateTime;
use DateTimeInterface;
use LogicException;

abstract class BaseQueryFilter implements QueryFilterInterface
{

    public function __construct(protected readonly mixed $query)
    {
    }

    public function getQuery(): mixed
    {
        return $this->query;
    }

    public function addDefaultFilter(string $field, string $value): void
    {
        throw new LogicException('No method defined to execute this type of filters');
    }

    /**
     * By default we call addDefaultFilter
     */
    public function __call(string $name, array $values = [])
    {
        if (preg_match('/add(.+)Filter/', $name)) {
            $this->addDefaultFilter($values[0], $values[1]);
        }
    }

    protected function formatDate(mixed $date, string $format): string|false
    {
        if ($date === null || $date === false) {
            return false;
        }

        return $date instanceof DateTimeInterface ? $date->format($format) : (new DateTime($date))->format($format);
    }
}
