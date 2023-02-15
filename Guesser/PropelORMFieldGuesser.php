<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\HttpKernel\Kernel;

class PropelORMFieldGuesser implements FieldGuesser
{
    private array $cache = [];

    public function __construct(
        private readonly array $formTypes,
        private readonly array $filterTypes,
        private readonly bool $guessRequired,
        private readonly bool$defaultRequired
    )
    {
    }

    protected function getMetadatas(string $class): mixed
    {
        return $this->getTable($class);
    }

    public function getAllFields(string $class): array
    {
        $return = array();

        foreach ($this->getMetadatas($class)->getColumns() as $column) {
            $return[] = InflectorFactory::create()->build()->tableize($column->getPhpName());
        }

        return $return;
    }

    public function getManyToMany(string $model, string $fieldPath): bool
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $relation = $this->getRelation($resolved['field'], $resolved['class']);

        if ($relation) {
            return \RelationMap::MANY_TO_MANY === $relation->getType();
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
    public function getDbType(string $model, string $fieldPath): string
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

    protected function getRelation(string $fieldName, string $class): object|false
    {
        $table = $this->getMetadatas($class);
        $relName = InflectorFactory::create()->build()->classify($fieldName);

        foreach ($table->getRelations() as $relation) {
            if ($relName === $relation->getName() || $relName === $relation->getPluralName()) {
                return $relation;
            }
        }

        return false;
    }

    /**
     * @return string|void
     */
    public function getPhpName(string $class, string $fieldName)
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
    public function getSortType(string $dbType): string
    {
        $alphabeticTypes = [
            \PropelColumnTypes::CHAR,
            \PropelColumnTypes::VARCHAR,
            \PropelColumnTypes::LONGVARCHAR,
            \PropelColumnTypes::BLOB,
            \PropelColumnTypes::CLOB,
            \PropelColumnTypes::CLOB_EMU,
        ];

        $numericTypes = [
            \PropelColumnTypes::FLOAT,
            \PropelColumnTypes::REAL,
            \PropelColumnTypes::DOUBLE,
            \PropelColumnTypes::DECIMAL,
            \PropelColumnTypes::TINYINT,
            \PropelColumnTypes::SMALLINT,
            \PropelColumnTypes::INTEGER,
            \PropelColumnTypes::BIGINT,
            \PropelColumnTypes::NUMERIC,
        ];

        if (in_array($dbType, $alphabeticTypes)) {
            return 'alphabetic';
        }

        if (in_array($dbType, $numericTypes)) {
            return 'numeric';
        }

        return 'default';
    }

    public function getFormType(string $dbType, string $class, string $columnName): string
    {
        $formTypes = array();

        foreach ($this->formTypes as $key => $value) {
            // if config is all uppercase use it to retrieve \PropelColumnTypes
            // constant, otherwise use it literally
            if ($key === strtoupper($key)) {
                $key = constant('\PropelColumnTypes::'.$key);
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

    public function getFilterType(string $dbType, string $class, string $columnName): string
    {
        $filterTypes = array();

        foreach ($this->filterTypes as $key => $value) {
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

    public function getFormOptions(string $formType, string $dbType, string $model, string $fieldPath): array
    {
        return $this->getOptions($formType, $dbType, $model, $fieldPath);
    }

    public function getFilterOptions(string $filterType, string $dbType, string $model, string $fieldPath): array
    {
        return $this->getOptions($filterType, $dbType, $model, $fieldPath, true);
    }

    protected function getOptions(string $type, string $dbType, string $model, string $fieldPath, bool $filter = false): array
    {
        if ('virtual' === $dbType) {
            return [];
        }

        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $columnName = $resolved['field'];

        if ((\PropelColumnTypes::BOOLEAN == $dbType || \PropelColumnTypes::BOOLEAN_EMU == $dbType) &&
            preg_match("#ChoiceType$#i", $type)) {
            $options = [
                'choices' => [
                    'boolean.no' => 0,
                    'boolean.yes' => 1
                ],
                'placeholder' => 'boolean.yes_or_no',
                'translation_domain' => 'Admingenerator',
                'choice_translation_domain' => 'Admingenerator'
            ];

            if (Kernel::MAJOR_VERSION < 3) {
                $options['choices_as_values'] = true;
            }

            return $options;
        }

        if (!$filter &&
            (\PropelColumnTypes::BOOLEAN == $dbType || \PropelColumnTypes::BOOLEAN_EMU == $dbType) &&
            preg_match("#CheckboxType#i", $type)) {
            return [
                'required' => false
            ];
        }

        if (preg_match("#ModelType$#i", $type)) {
            $relation = $this->getRelation($columnName, $class);
            if ($relation) {
                if (\RelationMap::MANY_TO_ONE === $relation->getType()) {
                    return [
                        'class'     => $relation->getForeignTable()->getClassname(),
                        'multiple'  => false,
                    ];
                }

                return [
                    'class'     => $relation->getLocalTable()->getClassname(),
                    'multiple'  => false,
                ];
            }
        }

        if (preg_match("#CollectionType$#i", $type)) {
            $relation = $this->getRelation($columnName, $class);

            if ($relation) {
                return [
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                    'entry_type' => 'entity',
                    'entry_options' => [
                        'class' => \RelationMap::MANY_TO_ONE === $relation->getType() ? $relation->getForeignTable()->getClassname() : $relation->getLocalTable()->getClassname()
                    ]
                ];
            }
            
            return [
                    'entry_type' => 'text',
            ];
        }

        if (\PropelColumnTypes::ENUM == $dbType) {
            $valueSet = $this->getMetadatas($class)->getColumn($columnName)->getValueSet();

            return [
                'required' => !$filter && $this->isRequired($class, $columnName),
                'choices'  => array_combine($valueSet, $valueSet),
            ];
        }

        return ['required' => !$filter && $this->isRequired($class, $columnName)];
    }

    protected function isRequired(string $class, string $fieldName): bool
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
    public function getModelPrimaryKeyName(string $class): ?string
    {
        $pks = $this->getMetadatas($class)->getPrimaryKeyColumns();

        if (count($pks) == 1) {
            return $pks[0]->getPhpName();
        }

        throw new \LogicException('No valid primary keys found');
    }

    protected function getTable(string $class): string
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

    protected function getColumn(string $class, string $property): mixed
    {
        if (isset($this->cache[$class.'::'.$property])) {
            return $this->cache[$class.'::'.$property];
        }

        $table = $this->getTable($class);

        if ($table && $table->hasColumn($property)) {
            return $this->cache[$class.'::'.$property] = $table->getColumn($property);
        } else {
            foreach ($table->getColumns() as $column) {
                $tabelized = InflectorFactory::create()->build()->tableize($column->getPhpName());
                if ($tabelized === $property || $column->getPhpName() === ucfirst($property)) {
                    return $this->cache[$class.'::'.$property] = $column;
                }
            }
        }
        return null;
    }

    /**
     * Find out the primary key for given model field path.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return string The leaf field's primary key.
     */
    public function getPrimaryKeyFor(string $model, string $fieldPath): ?string
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
    private function resolveRelatedField(string $model, string $fieldPath): array
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

        return [
            'field' => $field,
            'class' => $class
        ];
    }
}
