<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

use Doctrine\Inflector\InflectorFactory;

class PropelQueryFilter extends BaseQueryFilter
{
    protected array $joins = [];

    public function addDefaultFilter(string $field, mixed $value): void
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $method = 'filterBy'.InflectorFactory::create()->build()->classify($filteredField);
            $query->$method($value);
        } elseif (count($value) > 0) {
            $query->filterBy($filteredField, $value, \Criteria::IN);
        }
    }

    public function addBooleanFilter(string $field, bool $value): void
    {
        $this->addDefaultFilter($field, $value);
    }

    public function addVarcharFilter(string $field, string $value): void
    {
        $this->addDefaultFilter($field, '%'.$value.'%');
    }

    public function addCollectionFilter(string $field, mixed $value): void
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $value = [$value->getId()];
        }

        $query->filterBy($filteredField, $value, \Criteria::IN)
              ->endUse()
              ->groupById();
    }

    public function addDateFilter(string $field, mixed $value, string $format = 'Y-m-d'): void
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (is_string($value)) {
            if (!str_contains($value, ' - ') && false !== $date = $this->formatDate($value, $format)) {
                $query->filterBy($filteredField, $date);
            } else {
                // manage date range as a string
                $values = preg_split('/\s+-\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

                $from = $this->formatDate($values[0], $format);
                $to = $this->formatDate($values[1], $format);

                $value = ['from' => $from, 'to' => $to];
            }
        }

        if (is_array($value)) {
            $filters = [];

            if (array_key_exists('from', $value) && $from = $this->formatDate($value['from'], $format)) {
                $filters['min'] = $from;
            }

            if (array_key_exists('to', $value) && $to = $this->formatDate($value['to'], $format)) {
                $filters['max'] = $to;
            }

            if (count($filters) > 0) {
                $method = 'filterBy'.InflectorFactory::create()->build()->classify($filteredField);
                $query->$method($filters);
            }

        }
    }

    public function addTimestampFilter(string $field, mixed $value): void
    {
        $this->addDateFilter($field, $value);
    }

    public function addNullFilter(string $field): void
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        $query->filterBy($filteredField, null, \Criteria::EQUAL);
    }

    public function addNotNullFilter(string $field): void
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        $query->filterBy($filteredField, null, \Criteria::NOT_EQUAL);
    }

    protected function addTablePathToField(string $field): array
    {
        if (!strpos($field, '.')) {
            return [$this->query, $field];
        }

        $fieldParts = explode('.', $field);
        $filteredField = array_pop($fieldParts);
        $parentQuery = $this->query;

        foreach ($fieldParts as $field) {
            $joinAlias = $field . '_table_filter_join';
            if (!array_key_exists($joinAlias, $this->joins)) {
                $this->joins[$joinAlias] = call_user_func_array([$parentQuery, 'use'.$field.'Query'], [$field, \Criteria::INNER_JOIN]);
            }
            $parentQuery = $this->joins[$joinAlias];
        }

        return [$parentQuery, $filteredField];
    }
}
