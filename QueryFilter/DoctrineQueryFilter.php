<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

class DoctrineQueryFilter extends BaseQueryFilter
{
    /**
     * @var array
     */
    protected $joins = array();

    /**
     * @var integer
     */
    protected $paramNumber = 0;

    /**
     * @param string $field Table field name.
     * @return string Parameter name with unique key.
     */
    public function getParamName($field)
    {
        return $field.'_'.$this->paramNumber++;
    }

    public function addDefaultFilter($field, $value)
    {
        list($tableAlias, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $paramName = $this->getParamName($tableAlias.'_'.$filteredField);
            $this->query->andWhere(sprintf('%s.%s = :%s', $tableAlias, $filteredField, $paramName));
            $this->query->setParameter($paramName, $value);
        } elseif (count($value) > 0) {
            $paramName = $this->getParamName($tableAlias.'_'.$filteredField);
            $this->query->andWhere(sprintf('%s.%s IN (:%s)', $tableAlias, $filteredField, $paramName));
            $this->query->setParameter($paramName, $value);
        }
    }

    /**
     * @param string $field
     * @param boolean $value
     */
    public function addBooleanFilter($field, $value)
    {
        if ("" !== $value) {
            list($tableAlias, $filteredField) = $this->addTablePathToField($field);

            $paramName = $this->getParamName($tableAlias.'_'.$filteredField);
            $this->query->andWhere(sprintf('%s.%s = :%s', $tableAlias, $filteredField, $paramName));
            $this->query->setParameter($paramName, !!$value);
        }
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function addStringFilter($field, $value)
    {

        list($tableAlias, $filteredField) = $this->addTablePathToField($field);

        $paramName = $this->getParamName($tableAlias.'_'.$filteredField);
        $this->query->andWhere(sprintf('%s.%s LIKE :%s', $tableAlias, $filteredField, $paramName));
        if ($value === '') {
            $this->query->setParameter($paramName, '');
        } else {
            $this->query->setParameter($paramName, '%' . $value . '%');
        }
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function addTextFilter($field, $value)
    {
        $this->addStringFilter($field, $value);
    }

    public function addCollectionFilter($field, $value, $manyToMany = false)
    {
        list($tableAlias, $filteredField) = $this->addTablePathToField($field);
        if (!is_array($value)) {
            $value = array($value->getId());
        }

        $paramName = $this->getParamName($tableAlias.'_'.$filteredField);

        if ($manyToMany) {
            if (!in_array($filteredField, $this->joins)) {
                $this->query->join($tableAlias . '.' . $filteredField, $filteredField . '_table_filter_join');
            }
            $this->query->andWhere(sprintf('%s.%s IN (:%s)', $filteredField . '_table_filter_join', 'id', $paramName));
        } else {
            $this->query->andWhere(sprintf('%s.%s IN (:%s)', $tableAlias, $filteredField, $paramName));
        }
        $this->query->groupBy('q');
        $this->query->setParameter($paramName, $value);

    }

    public function addDateFilter($field, $value, $format = 'Y-m-d')
    {
        list($tableAlias, $filteredField) = $this->addTablePathToField($field);

        if (is_array($value)) {
            if (array_key_exists('from', $value)) {
                if (false !== $from = $this->formatDate($value['from'], $format)) {
                    $paramName = $this->getParamName($tableAlias.'_'.$filteredField.'_from');
                    $this->query->andWhere(sprintf('%s.%s >= :%s_from', $tableAlias, $filteredField, $paramName));
                    $this->query->setParameter($paramName, $from);
                }
            }

            if (array_key_exists('to', $value)) {
                if (false !== $to = $this->formatDate($value['to'], $format)) {
                    $paramName = $this->getParamName($tableAlias.'_'.$filteredField.'_to');
                    $this->query->andWhere(sprintf('%s.%s <= :%s_to',$tableAlias, $filteredField, $paramName));
                    $this->query->setParameter($paramName, $to);
                }
            }

        } else {
            if (false !== $date = $this->formatDate($value, $format)) {
                $paramName = $this->getParamName($tableAlias.'_'.$filteredField);
                $this->query->andWhere(sprintf('s.%s = :%s', $tableAlias, $filteredField, $paramName));
                $this->query->setParameter($paramName, $date);
            }
        }
    }

    public function addDatetimeFilter($field, $value, $format = 'Y-m-d H:i:s')
    {
        $this->addDateFilter($field, $value, $format);
    }

    public function addNullFilter($field, $value = null)
    {
        list($tableAlias, $filteredField) = $this->addTablePathToField($field);

        $this->query->andWhere(sprintf('%s.%s IS NULL', $tableAlias, $filteredField));
    }

    public function addNotNullFilter($field, $value = null)
    {
        list($tableAlias, $filteredField) = $this->addTablePathToField($field);

        $this->query->andWhere(sprintf('%s.%s IS NOT NULL', $tableAlias, $filteredField));
    }

    protected function addTablePathToField($field)
    {
        if (!strpos($field, '.')) {
            return array('q', $field);
        }

        $fieldParts = explode('.', $field);
        $filteredField = array_pop($fieldParts);
        $parentTableAlias = 'q';

        foreach ($fieldParts as $field) {
            $joinAlias = $field . '_table_filter_join';
            if (!in_array($joinAlias, $this->joins)) {
                $this->query->join($parentTableAlias . '.' . $field, $joinAlias);
                $this->joins[] = $joinAlias;
            }
            $parentTableAlias = $joinAlias;
        }

        return array($parentTableAlias, $filteredField);
    }
}
