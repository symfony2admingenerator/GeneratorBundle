<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\DependencyInjection\ContainerAware;

class PropelORMFieldGuesser extends ContainerAware
{
    /**
     * @var boolean
     */
    private $guessRequired;

    /**
     * @var boolean
     */
    private $defaultRequired;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * @param $class
     * @return mixed
     */
    protected function getMetadatas($class)
    {
        return $this->getTable($class);
    }

    /**
     * @param $class
     * @return array
     */
    public function getAllFields($class)
    {
        $return = array();

        foreach ($this->getMetadatas($class)->getColumns() as $column) {
            $return[] = Inflector::tableize($column->getPhpName());
        }

        return $return;
    }

    /**
     * Find out the database type for given model field path.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return string The leaf field's primary key.
     */
    public function getDbType($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $field = $resolved['field'];

        $relation = $this->getRelation($field, $class);

        if ($relation) {
            return \RelationMap::MANY_TO_ONE === $relation->getType() ? 'model' : 'collection';
        }

        $column = $this->getColumn($class, $field);

        return $column ? $column->getType() : 'virtual';
    }

    /**
     * @param $fieldName
     * @param $class
     * @return object|false The relation object or false.
     */
    protected function getRelation($fieldName, $class)
    {
        $table = $this->getMetadatas($class);
        $relName = Inflector::classify($fieldName);

        foreach ($table->getRelations() as $relation) {
            if ($relName === $relation->getName() || $relName === $relation->getPluralName()) {
                return $relation;
            }
        }

        return false;
    }

    /**
     * @param $class
     * @param $fieldName
     * @return string|void
     */
    public function getPhpName($class, $fieldName)
    {
        $column = $this->getColumn($class, $fieldName);

        if ($column) {
            return $column->getPhpName();
        }
    }

    /**
     * @param $dbType
     * @return string
     */
    public function getSortType($dbType)
    {
        $alphabeticTypes = array(
            \PropelColumnTypes::CHAR,
            \PropelColumnTypes::VARCHAR,
            \PropelColumnTypes::LONGVARCHAR,
            \PropelColumnTypes::BLOB,
            \PropelColumnTypes::CLOB,
            \PropelColumnTypes::CLOB_EMU,
        );

        $numericTypes = array(
            \PropelColumnTypes::FLOAT,
            \PropelColumnTypes::REAL,
            \PropelColumnTypes::DOUBLE,
            \PropelColumnTypes::DECIMAL,
            \PropelColumnTypes::TINYINT,
            \PropelColumnTypes::SMALLINT,
            \PropelColumnTypes::INTEGER,
            \PropelColumnTypes::BIGINT,
            \PropelColumnTypes::NUMERIC,
        );

        if (in_array($dbType, $alphabeticTypes)) {
            return 'alphabetic';
        }

        if (in_array($dbType, $numericTypes)) {
            return 'numeric';
        }

        return 'default';
    }

    /**
     * @param $dbType
     * @param $class: for debug only
     * @param $columnName: for debug only
     * @return string
     */
    public function getFormType($dbType, $class, $columnName)
    {
        $config = $this->container->getParameter('admingenerator.propel_form_types');
        $formTypes = array();

        foreach ($config as $key => $value) {
            // if config is all uppercase use it to retrieve \PropelColumnTypes
            // constant, otherwise use it literally
            if ($key === strtoupper($key)) {
                $key = constant('\PropelColumnTypes::'.$key);
            }

            $formTypes[$key] = $value;
        }

        if (array_key_exists($dbType, $formTypes)) {
            return $formTypes[$dbType];
        } elseif ('virtual' === $dbType) {
            return 'virtual_form';
        } else {
            throw new NotImplementedException(
                'The dbType "'.$dbType.'" is not yet implemented '
                .'(column "'.$columnName.'" in "'.$class.'")'
            );
        }
    }

    /**
     * @param $dbType
     * @param $columnName
     * @return string
     */
    public function getFilterType($dbType, $class, $columnName)
    {
        $config = $this->container->getParameter('admingenerator.propel_filter_types');
        $filterTypes = array();

        foreach ($config as $key => $value) {
            // if config is all uppercase use it to retrieve \PropelColumnTypes
            // constant, otherwise use it literally
            if ($key === strtoupper($key)) {
                $key = constant('\PropelColumnTypes::'.$key);
            }

            $filterTypes[$key] = $value;
        }

        if (array_key_exists($dbType, $filterTypes)) {
            return $filterTypes[$dbType];
        }

        if ('virtual' === $dbType) {
            return 'virtual_filter';
        }

        throw new NotImplementedException(
            'The dbType "'.$dbType.'" is not yet implemented '
            .'(column "'.$columnName.'" in "'.$class.'")'
        );
    }

    /**
     * @param $formType
     * @param $dbType
     * @param $columnName
     * @return array
     */
    public function getFormOptions($formType, $dbType, $model, $fieldPath)
    {
        if ('virtual' === $dbType) {
            return array();
        }

        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $columnName = $resolved['field'];

        if ((\PropelColumnTypes::BOOLEAN == $dbType || \PropelColumnTypes::BOOLEAN_EMU == $dbType) &&
            (preg_match("#^choice#i", $formType) || preg_match("#choice$#i", $formType))) {
            return array(
                'choices' => array(
                   0 => 'boolean.no',
                   1 => 'boolean.yes'
                ),
                'empty_value' => 'boolean.yes_or_no',
                'translation_domain' => 'Admingenerator'
            );
        }

        if ((\PropelColumnTypes::BOOLEAN == $dbType || \PropelColumnTypes::BOOLEAN_EMU == $dbType) &&
            (preg_match("#^checkbox#i", $formType) || preg_match("#checkbox#i", $formType))) {
            return array(
                'required' => false
            );
        }

        if (preg_match("#^model#i", $formType) || preg_match("#model$#i", $formType)) {
            $relation = $this->getRelation($columnName, $class);
            if ($relation) {
                if (\RelationMap::MANY_TO_ONE === $relation->getType()) {
                    return array(
                        'class'     => $relation->getForeignTable()->getClassname(),
                        'multiple'  => false,
                    );
                }

                return array(
                    'class'     => $relation->getLocalTable()->getClassname(),
                    'multiple'  => false,
                );
            }
        }

        if (preg_match("#^collection#i", $formType) || preg_match("#collection$#i", $formType)) {
            $relation = $this->getRelation($columnName, $class);

            return array(
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'type' => 'entity',
                'options' => array(
                    'class' => \RelationMap::MANY_TO_ONE === $relation->getType() ? $relation->getForeignTable()->getClassname() : $relation->getLocalTable()->getClassname()
                )
            );
        }

        if (\PropelColumnTypes::ENUM == $dbType) {
            $valueSet = $this->getMetadatas($class)->getColumn($class, $columnName)->getValueSet();

            return array(
                'required' => $this->isRequired($class, $columnName),
                'choices'  => array_combine($valueSet, $valueSet),
            );
        }

        return array('required' => $this->isRequired($class, $columnName));
    }

    protected function isRequired($class, $fieldName)
    {
        if (!isset($this->guessRequired) || !isset($this->defaultRequired)) {
            $this->guessRequired = $this->container->getParameter('admingenerator.guess_required');
            $this->defaultRequired = $this->container->getParameter('admingenerator.default_required');
        }

        if (!$this->guessRequired) {
            return $this->defaultRequired;
        }

        $column = $this->getColumn($class, $fieldName);

        return $column ? $column->isNotNull() : false;
    }

    /**
     * Find the pk name
     */
    public function getModelPrimaryKeyName($class)
    {
        $pks = $this->getMetadatas($class)->getPrimaryKeyColumns();

        if (count($pks) == 1) {
            return $pks[0]->getPhpName();
        }

        throw new \LogicException('No valid primary keys found');
    }

    protected function getTable($class)
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        if (class_exists($queryClass = $class.'Query')) {
            $query = new $queryClass();

            return $this->cache[$class] = $query->getTableMap();
        }

        throw new \LogicException('Can\'t find query class '.$queryClass);
    }

    protected function getColumn($class, $property)
    {
        if (isset($this->cache[$class.'::'.$property])) {
            return $this->cache[$class.'::'.$property];
        }

        $table = $this->getTable($class);

        if ($table && $table->hasColumn($property)) {
            return $this->cache[$class.'::'.$property] = $table->getColumn($property);
        } else {
            foreach ($table->getColumns() as $column) {
                $tabelized = Inflector::tableize($column->getPhpName());
                if ($tabelized === $property || $column->getPhpName() === ucfirst($property)) {
                    return $this->cache[$class.'::'.$property] = $column;
                }
            }
        }
    }

    /**
     * Find out the primary key for given model field path.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return string The leaf field's primary key.
     */
    public function getPrimaryKeyFor($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $field = $resolved['field'];

        if ($relation = $this->getRelation($field, $class)) {
            $class = $relation->getName();

            return $this->getModelPrimaryKeyName($class);
        } else {
            // if the leaf node is not an association
            return null;
        }
    }

    /**
     * Resolve field path for given model to class and field name.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return array  An array containing field and class information.
     */
    private function resolveRelatedField($model, $fieldPath)
    {
        $path = explode('.', $fieldPath);
        $field = array_pop($path);
        $class = $model;

        foreach ($path as $part) {
            if (!$relation = $this->getRelation($part, $class)) {
                throw new \LogicException('Field "'.$part.'" for class "'.$class.'" is not an association.');
            }

            $class = $relation->getName();
        }

        return array(
            'field' => $field,
            'class' => $class
        );
    }
}
