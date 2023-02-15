<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

class DoctrineODMQueryFilter extends BaseQueryFilter
{

    public function addDefaultFilter(string $field, mixed $value): void
    {
        if (!is_array($value)) {
            $this->query->field($field)->equals($value);
        } elseif (count($value) > 0) {
            $this->query->field($field)->in($value);
        }
    }

    public function addStringFilter(string $field, string $value): void
    {
        $this->query->field($field)->equals(new \MongoRegex("/.*$value.*/i"));
    }

    public function addBooleanFilter(string $field, bool $value)
    {
        $this->query->field($field)->equals($value);
    }

    public function addDateFilter(string $field, mixed $value, string $format = 'Y-m-d'): void
    {
        if (is_array($value)) {
            $from = array_key_exists('from', $value) ? $this->formatDate($value['from'], $format) : false;
            $to   = array_key_exists('to',   $value) ? $this->formatDate($value['to'],   $format) : false;

            if ($to && $from) {
                $this->query->field($field)->range($from, $to);
            } elseif ($from) {
                $this->query->field($field)->gte($from);
            } elseif ($to) {
                $this->query->field($field)->lte($to);
            }
        } else {
            if (false !== $date = $this->formatDate($value, $format)) {
                $this->query->field($field)->equals($date);
            }
        }
    }

    public function addDocumentFilter(string $field, mixed $value): void
    {
         $this->query->field($field.'.$id')->equals(new \MongoId($value->getId()));
    }

    public function addCollectionFilter(string $field, mixed $value): void
    {
         $this->query->field($field.'.$id')->equals(new \MongoId($value->getId()));
    }

    public function addNullFilter(string $field): void
    {
        $this->query->field($field)->equals(null);
    }

    public function addNotNullFilter(string $field): void
    {
        $this->query->field($field)->notEqual(null);
    }
}
