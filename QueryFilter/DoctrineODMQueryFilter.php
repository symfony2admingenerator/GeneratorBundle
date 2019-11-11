<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

use Doctrine\ODM\MongoDB\Query\Builder;

class DoctrineODMQueryFilter extends BaseQueryFilter
{
    /**
     * @var Builder
     */
    protected $query;

    public function addDefaultFilter($field, $value)
    {
        if (!is_array($value)) {
            $this->query->field($field)->equals($value);
        } elseif (count($value) > 0) {
            $this->query->field($field)->in($value);
        }
    }

    public function addStringFilter($field, $value)
    {
        $this->query->field($field)->equals(new \MongoRegex("/.*$value.*/i"));
    }

    public function addBooleanFilter($field, $value)
    {
        if ("" !== $value) {
            $this->query->field($field)->equals((boolean) $value);
        }
    }

    public function addDateFilter($field, $value, $format = 'Y-m-d')
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

    public function addDocumentFilter($field, $value)
    {
         $this->query->field($field.'.$id')->equals(new \MongoId($value->getId()));
    }

    public function addCollectionFilter($field, $value)
    {
         $this->query->field($field.'.$id')->equals(new \MongoId($value->getId()));
    }

    public function addNullFilter($field, $value = null)
    {
        $this->query->field($field)->equals(null);
    }

    public function addNotNullFilter($field, $value = null)
    {
        $this->query->field($field)->notEqual(null);
    }
}
