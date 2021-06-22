<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\HttpKernel\Kernel;

abstract class DoctrineFieldGuesser
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

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
    private $formTypes;

    /**
     * @var array
     */
    private $filterTypes;

    public function __construct(ManagerRegistry $registry, $objectModel, array $formTypes, array $filterTypes, $guessRequired, $defaultRequired)
    {
        if (!in_array($objectModel = strtolower($objectModel), array('document', 'entity'))) {
            throw new \InvalidArgumentException('$objectModel must be Document or Entity');
        }

        $this->registry = $registry;
        $this->objectModel = strtolower($objectModel);
        $this->formTypes = $formTypes;
        $this->filterTypes = $filterTypes;
        $this->guessRequired = $guessRequired;
        $this->defaultRequired = $defaultRequired;
    }

    protected function getMetadatas($class)
    {
        // Cache is implemented by Doctrine itself
        return $this->registry->getManagerForClass($class)->getClassMetadata($class);
    }

    public function getAllFields($class)
    {
        return array_merge($this->getMetadatas($class)->getFieldNames(), $this->getMetadatas($class)->getAssociationNames());
    }

    public function getManyToMany($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $metadata = $this->getMetadatas($resolved['class']);
        return $metadata->hasAssociation($resolved['field']) && !$metadata->isAssociationWithSingleJoinColumn($resolved['field']);
    }

    /**
     * Find out the database type for given model field path.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return string The DB type for given model field path.
     */
    public function getDbType($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $field = $resolved['field'];

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($field)) {
            if ($metadata->isSingleValuedAssociation($field)) {
                return $this->objectModel;
            }

            return 'collection';
        }

        if ($metadata->hasField($field)) {
            return $metadata->getTypeOfField($field);
        }

        return 'virtual';
    }

    public function getModelType($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);

        $class = $resolved['class'];
        $fieldName = $resolved['field'];

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($fieldName)) {
            return $metadata->getAssociationTargetClass($fieldName);
        }

        if ($metadata->hasField($fieldName)) {
            return $metadata->getTypeOfField($fieldName);
        }

        return 'virtual';
    }

    public function getSortType($dbType)
    {
        $alphabeticTypes = array(
            'string',
            'text',
            'id',
            'custom_id',
        );

        $numericTypes = array(
            'decimal',
            'float',
            'int',
            'integer',
            'int_id',
            'bigint',
            'smallint',
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
        if (array_key_exists($dbType, $this->formTypes)) {
            return $this->formTypes[$dbType];
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
     * @param $class: for debug only
     * @param $columnName: for debug only
     * @return string
     */
    public function getFilterType($dbType, $class, $columnName)
    {
        if (array_key_exists($dbType, $this->filterTypes)) {
            return $this->filterTypes[$dbType];
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    protected function getOptions($type, $dbType, $model, $fieldPath, $filter = false)
    {
        if ('virtual' === $dbType) {
            return array();
        }

        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $columnName = $resolved['field'];

        if ('boolean' == $dbType && preg_match("/ChoiceType$/i", $type)) {
            $options = array(
                'choices' => array(
                    'boolean.no' => 0,
                    'boolean.yes' => 1
                ),
                'placeholder' => 'boolean.yes_or_no',
                'translation_domain' => 'Admingenerator',
                'choice_translation_domain' => 'Admingenerator'
            );

            if (Kernel::MAJOR_VERSION < 3) {
                $options['choices_as_values'] = true;
            }

            return $options;
        }

        if ('boolean' == $dbType && preg_match("/CheckboxType/i", $type)) {
            return array(
                'required' => false,
            );
        }

        if (preg_match("/NumberType/i", $type)) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            if (isset($mapping['scale'])) {
                $scale = $mapping['scale'];
            }

            if (isset($mapping['precision'])) {
                $scale = $mapping['precision'];
            }

            return array(
                'scale' => isset($scale) ? $scale : null,
                'required'  => $filter ? false : $this->isRequired($class, $columnName)
            );
        }

        if (preg_match(sprintf('/%sType$/i', ucfirst($this->objectModel)), $type)) {
            $mapping = $this->getMetadatas($class)->getAssociationMapping($columnName);

            return array(
                'multiple'      => ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY || $mapping['type'] === ClassMetadataInfo::ONE_TO_MANY),
                'em'            => $this->getObjectManagerName($mapping['target'.ucfirst($this->objectModel)]),
                'class'         => $mapping['target'.ucfirst($this->objectModel)],
                'required'      => $filter ? false : $this->isRequired($class, $columnName),
            );
        }

        if (preg_match("/CollectionType$/i", $type)) {
            $options = array(
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'entry_type' => $filter ? $this->filterTypes[$this->objectModel] : $this->formTypes[$this->objectModel],
            );

            if ($this->getMetadatas($class)->hasAssociation($columnName)) {
                $mapping = $this->getMetadatas($class)->getAssociationMapping($columnName);
                $options['entry_options'] = array(
                    'class' => $mapping['target'.ucfirst($this->objectModel)]
                );
            }

            return $options;
        }

        return array(
            'required' => $filter ? false : $this->isRequired($class, $columnName)
        );
    }

    protected function isRequired($class, $fieldName)
    {
        if (!$this->guessRequired) {
            return $this->defaultRequired;
        }

        $metadata = $this->getMetadatas($class);

        $hasField = $metadata->hasField($fieldName);
        $hasAssociation = $metadata->hasAssociation($fieldName);
        $isSingleValAssoc = $metadata->isSingleValuedAssociation($fieldName);

        if ($hasField && (!$hasAssociation || $isSingleValAssoc)) {
            return !$metadata->isNullable($fieldName);
        }

        return false;
    }

    /**
     * Find the pk name for given class
     *
     * @param  string $class The class name.
     * @return string Primary key field name.
     */
    public function getModelPrimaryKeyName($class)
    {
        $identifierFieldNames = $this->getMetadatas($class)->getIdentifierFieldNames();

        return !empty($identifierFieldNames) ? $identifierFieldNames[0] : null;
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

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($field)) {
            $class = $metadata->getAssociationTargetClass($field);
            $classIdentifiers = $this->getMetadatas($class)->getIdentifierFieldNames();

            // Short workaround for https://github.com/symfony2admingenerator/GeneratorBundle/issues/161
            // TODO: throw an exception to correctly handle that situation?
            return 1 == count($classIdentifiers) ? $classIdentifiers[0] : null;
        }

        // if the leaf node is not an association
        return null;
    }

    /**
     * Resolve field path for given model to class and field name.
     *
     * @param  string $model     The starting model.
     * @param  string $fieldPath The field path.
     * @return array  An array containing field and class information.
     */
    protected function resolveRelatedField($model, $fieldPath)
    {
        $path = explode('.', $fieldPath);
        $field = array_pop($path);
        $class = $model;

        foreach ($path as $part) {
            $metadata = $this->getMetadatas($class);

            if (!$metadata->hasAssociation($part)) {
                throw new \LogicException('Field "'.$part.'" for class "'.$class.'" is not an association.');
            }

            $class = $metadata->getAssociationTargetClass($part);
        }

        return array(
            'field' => $field,
            'class' => $class
        );
    }

    /**
     * Retrieve Doctrine EntityManager name for class $className
     *
     * @param $className
     * @return int|string
     * @throws \Exception
     */
    protected function getObjectManagerName($className)
    {
        $om = $this->registry->getManagerForClass($className);
        foreach ($this->registry->getManagerNames() as $emName=>$omName)
        {
            $instance = $this->registry->getManager($emName);
            if ($instance == $om)
            {
                return $emName;
            }
        }

        throw new \Exception("Object manager for class: $className not found.");
    }
}
