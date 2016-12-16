<?php

namespace Admingenerator\GeneratorBundle\QueryFilter;

use Propel\Runtime\ActiveQuery\Criteria;
use Doctrine\Common\Util\Inflector;

class Propel2QueryFilter extends PropelQueryFilter
{

    public function addDefaultFilter($field, $value, $criteria = null)
    {
        list($query, $filteredField) = $this->addTablePathToField($field);

        if (!is_array($value)) {
            $method = 'filterBy'.Inflector::classify($filteredField);
            $query->$method($value, $criteria);
        } elseif (count($value) > 0) {
            $query->filterBy($filteredField, $value, Criteria::IN);
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
        $this->addDefaultFilter($field, '%'.$value.'%',  Criteria::LIKE);
    }
    
}
