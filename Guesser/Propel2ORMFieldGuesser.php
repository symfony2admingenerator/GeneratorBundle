<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\HttpKernel\Kernel;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\RelationMap;

class Propel2ORMFieldGuesser
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
     * @var array
     */
    private $formTypes;

    /**
     * @var array
     */
    private $filterTypes;

    public function __construct(array $formTypes, array $filterTypes, $guessRequired, $defaultRequired)
    {
        $this->formTypes = $formTypes;
        $this->filterTypes = $filterTypes;
        $this->guessRequired = $guessRequired;
        $this->defaultRequired = $defaultRequired;
    }

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

    public function getManyToMany($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $relation = $this->getRelation($resolved['field'], $resolved['class']);

        if ($relation) {
            return RelationMap::MANY_TO_MANY === $relation->getType();
        }

        return false;
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
            return RelationMap::MANY_TO_ONE === $relation->getType() ? 'model' : 'collection';
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
            PropelTypes::CHAR,
            PropelTypes::VARCHAR,
            PropelTypes::LONGVARCHAR,
            PropelTypes::BLOB,
            PropelTypes::CLOB,
            PropelTypes::CLOB_EMU,
        );

        $numericTypes = array(
            PropelTypes::FLOAT,
            PropelTypes::REAL,
            PropelTypes::DOUBLE,
            PropelTypes::DECIMAL,
            PropelTypes::TINYINT,
            PropelTypes::SMALLINT,
            PropelTypes::INTEGER,
            PropelTypes::BIGINT,
            PropelTypes::NUMERIC,
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
        $formTypes = array();

        foreach ($this->formTypes as $key => $value) {
            // if config is all uppercase use it to retrieve PropelTypes
            // constant, otherwise use it literally
            if ($key === strtoupper($key)) {
                $key = constant('\Propel\Generator\Model\PropelTypes::'.$key);
            }

            $formTypes[$key] = $value;
        }

        if (array_key_exists($dbType, $formTypes)) {
            return $formTypes[$dbType];
        }

        if ('virtual' === $dbType) {
            return 'virtual_form';
        }

        throw new NotImplementedException(
            'The dbType "'.$dbType.'" is not yet implemented '
            .'(column "'.$columnName.'" in "'.$class.'")'
        );
    }

    /**
     * @param $dbType
     * @param $columnName
     * @return string
     */
    public function getFilterType($dbType, $class, $columnName)
    {
        $filterTypes = array();

        foreach ($this->filterTypes as $key => $value) {
            // if config is all uppercase use it to retrieve PropelTypes
            // constant, otherwise use it literally
            if ($key === strtoupper($key)) {
                $key = constant('\Propel\Generator\Model\PropelTypes::'.$key);
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
     * @param $model
     * @param $fieldPath
     * @return array
     */
    public function getFormOptions($formType, $dbType, $model, $fieldPath)
    {
        return $this->getOptions($formType, $dbType, $model, $fieldPath, false);
    }

    /**
     * @param $filterType
     * @param $dbType
     * @param $model
     * @param $fieldPath
     * @return array
     */
    public function getFilterOptions($filterType, $dbType, $model, $fieldPath)
    {
        return $this->getOptions($filterType, $dbType, $model, $fieldPath, true);
    }

    /**
     * @param      $type
     * @param      $dbType
     * @param      $model
     * @param      $fieldPath
     * @param bool $filter
     * @return array
     */
    protected function getOptions($type, $dbType, $model, $fieldPath, $filter = false)
    {
        if ('virtual' === $dbType) {
            return array();
        }

        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $columnName = $resolved['field'];

        if ((PropelTypes::BOOLEAN == $dbType || PropelTypes::BOOLEAN_EMU == $dbType) &&
            preg_match("#ChoiceType$#i", $type)) {
            $options = array(
                'choices' => array(
                    'boolean.no' => 0,
                    'boolean.yes' => 1
                ),
                'placeholder' => 'boolean.yes_or_no',
                'translation_domain' => 'Admingenerator'
            );

            if (Kernel::MAJOR_VERSION < 3) {
                $options['choices_as_values'] = true;
            }

            return $options;
        }

        if (!$filter &&
            (PropelTypes::BOOLEAN == $dbType || PropelTypes::BOOLEAN_EMU == $dbType) &&
            preg_match("#CheckboxType#i", $type)) {
            return array(
                'required' => false
            );
        }

        if (preg_match("#ModelType$#i", $type)) {
            $relation = $this->getRelation($columnName, $class);
            if ($relation) {
                if (RelationMap::MANY_TO_ONE === $relation->getType()) {
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

        if (preg_match("#CollectionType$#i", $type)) {
            $relation = $this->getRelation($columnName, $class);

            if ($relation) {
                return array(
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'entry_type' => 'entity',
                    'entry_options' => array(
                        'class' => RelationMap::MANY_TO_ONE === $relation->getType() ? $relation->getForeignTable()->getClassname() : $relation->getLocalTable()->getClassname()
                    )
                );
            }

            return array(
                    'entry_type' => 'text',
                );
        }

        if (PropelTypes::ENUM == $dbType) {
            $valueSet = $this->getMetadatas($class)->getColumn($columnName)->getValueSet();

            return array(
                'required' => $filter ? false : $this->isRequired($class, $columnName),
                'choices'  => array_combine($valueSet, $valueSet),
            );
        }

        return array('required' => $filter ? false : $this->isRequired($class, $columnName));
    }

    protected function isRequired($class, $fieldName)
    {
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
        $pks = $this->getMetadatas($class)->getPrimaryKeys();

        if (count($pks) == 1) {
            return $pks[key($pks)]->getPhpName();
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
            $class = $relation->getLocalTable()->getClassname();

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
