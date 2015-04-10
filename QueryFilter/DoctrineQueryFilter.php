<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

class DoctrineQueryFilter extends BaseQueryFilter
{
    /**
     * @var integer
     */
    protected $paramNumber = 0;

    /**
     * @param string $field Table field name.
     * @return string Param key unique name.
     */
    public function getParamName($field)
    {
        return $field.'_'.$this->paramNumber++;
    }
    
    public function addDefaultFilter($field, $value)
    {
        $paramName = $this->getParamName($field);
        if (!is_array($value)) {
            $this->query->andWhere(sprintf('q.%s = :%s', $field, $paramName));
            $this->query->setParameter($paramName, $value);
        } elseif (count($value) > 0) {
            $this->query->andWhere(sprintf('q.%s IN (:%s)', $field, $paramName));
            $this->query->setParameter($paramName, $value);
        }
    }

    public function addBooleanFilter($field, $value)
    {
        $paramName = $this->getParamName($field);

        if ("" !== $value) {
            $this->query->andWhere(sprintf('q.%s = :%s', $field, $paramName));
            $this->query->setParameter($paramName, !!$value);
        }
    }

    public function addStringFilter($field, $value)
    {
        $paramName = $this->getParamName($field);

        $this->query->andWhere(sprintf('q.%s LIKE :%s', $field, $paramName));
        $this->query->setParameter($paramName, '%'.$value.'%');
    }

    public function addTextFilter($field, $value)
    {
        $this->addStringFilter($field, $value);
    }

    public function addCollectionFilter($field, $value)
    {
        if (!is_array($value)) {
            $value = array($value->getId());
        }

        if (strstr($field, '.')) {
            list($table, $field) = explode('.', $field);
        } else {
            $table = $field;
            $field = 'id';
        }

        $paramName = $this->getParamName($table.'_'.$field);

        $this->query->leftJoin('q.'.$table, $table);
        $this->query->groupBy('q');
        $this->query->andWhere(sprintf('%s.%s IN (:%s)',$table, $field, $paramName));
        $this->query->setParameter($paramName, $value);

    }

    public function addDateFilter($field, $value, $format = 'Y-m-d')
    {
        if (is_array($value)) {
            if (array_key_exists('from', $value)) {
                if (false !== $from = $this->formatDate($value['from'], $format)) {
                    $paramName = $this->getParamName($field.'_from');
                    $this->query->andWhere(sprintf('q.%s >= :%s_from', $field, $paramName));
                    $this->query->setParameter($paramName, $from);
                }
            }

            if (array_key_exists('to', $value)) {
                if (false !== $to = $this->formatDate($value['to'], $format)) {
                    $paramName = $this->getParamName($field.'_to');
                    $this->query->andWhere(sprintf('q.%s <= :%s_to', $field, $paramName));
                    $this->query->setParameter($paramName, $to);
                }
            }

        } else {
            if (false !== $date = $this->formatDate($value, $format)) {
                $paramName = $this->getParamName($field);
                $this->query->andWhere(sprintf('q.%s = :%s', $field, $paramName));
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
        $this->query->andWhere(sprintf('q.%s IS NULL', $field));
    }

    public function addNotNullFilter($field, $value = null)
    {
        $this->query->andWhere(sprintf('q.%s IS NOT NULL', $field));
    }
}
