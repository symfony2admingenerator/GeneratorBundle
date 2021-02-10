<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

use Doctrine\Inflector\InflectorFactory;

class PropelQueryFilter extends BaseQueryFilter
{
    /**
     * @var array
     */
    protected $joins = array();

    public function addDefaultFilter($field, $value)
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $method = 'filterBy'.InflectorFactory::create()->build()->classify($filteredField);
            $query->$method($value);
        } elseif (count($value) > 0) {
            $query->filterBy($filteredField, $value, \Criteria::IN);
        }
    }

    public function addBooleanFilter($field, $value)
    {
        if ("" !== $value) {
            $this->addDefaultFilter($field, $value);
        }
    }

    public function addVarcharFilter($field, $value)
    {
        $this->addDefaultFilter($field, '%'.$value.'%');
    }

    public function addCollectionFilter($field, $value)
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $value = array($value->getId());
        }

        $query->filterBy($filteredField, $value, \Criteria::IN)
              ->endUse()
              ->groupById();
    }

    public function addDateFilter($field, $value, $format = 'Y-m-d')
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (is_string($value)) {
            if (false === strpos($value, ' - ') && false !== $date = $this->formatDate($value, $format)) {
                $query->filterBy($filteredField, $date);
            } else {
                // manage date range as a string
                $values = preg_split('/\s+-\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);

                $from = $this->formatDate($values[0], $format);
                $to = $this->formatDate($values[1], $format);

                $value = array('from' => $from, 'to' => $to);
            }
        }

        if (is_array($value)) {
            $filters = array();

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

    public function addTimestampFilter($field, $value)
    {
        return $this->addDateFilter($field, $value);
    }

    public function addNullFilter($field, $value = null)
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        $query->filterBy($filteredField, null, \Criteria::EQUAL);
    }

    public function addNotNullFilter($field, $value = null)
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        $query->filterBy($filteredField, null, \Criteria::NOT_EQUAL);
    }

    protected function addTablePathToField($field)
    {
        if (!strpos($field, '.')) {
            return array($this->query, $field);
        }

        $fieldParts = explode('.', $field);
        $filteredField = array_pop($fieldParts);
        $parentQuery = $this->query;

        foreach ($fieldParts as $field) {
            $joinAlias = $field . '_table_filter_join';
            if (!array_key_exists($joinAlias, $this->joins)) {
                $this->joins[$joinAlias] = call_user_func_array(array($parentQuery, 'use'.$field.'Query'), array($field, \Criteria::INNER_JOIN));
            }
            $parentQuery = $this->joins[$joinAlias];
        }

        return array($parentQuery, $filteredField);
    }
}
