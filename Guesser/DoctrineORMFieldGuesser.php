<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\ContainerAware;

class DoctrineORMFieldGuesser extends ContainerAware
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var boolean
     */
    private $guessRequired;

    /**
     * @var boolean
     */
    private $defaultRequired;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    protected function getMetadatas($class)
    {
        // Cache is implemented by Doctrine itself
        return $this->doctrine->getManagerForClass($class)->getClassMetadata($class);
    }

    public function getAllFields($class)
    {
        return array_merge($this->getMetadatas($class)->getFieldNames(), $this->getMetadatas($class)->getAssociationNames());
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
                return 'entity';
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
        );

        $numericTypes = array(
            'decimal',
            'float',
            'integer',
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
        $formTypes = $this->container->getParameter('admingenerator.doctrine_form_types');

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
     * @param $class: for debug only
     * @param $columnName: for debug only
     * @return string
     */
    public function getFilterType($dbType, $class, $columnName)
    {
        $filterTypes = $this->container->getParameter('admingenerator.doctrine_filter_types');

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
     * @throws \Exception
     */
    public function getFormOptions($formType, $dbType, $model, $fieldPath)
    {
        if ('virtual' === $dbType) {
            return array();
        }

        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $class = $resolved['class'];
        $columnName = $resolved['field'];

        if ('boolean' == $dbType &&
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

        if ('number' === $formType) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            if (isset($mapping['scale'])) {
                $precision = $mapping['scale'];
            }

            if (isset($mapping['precision'])) {
                $precision = $mapping['precision'];
            }

            return array(
                'precision' => isset($precision) ? $precision : '',
                'required'  => $this->isRequired($class, $columnName)
            );
        }

        if (preg_match("#^entity#i", $formType) || preg_match("#entity$#i", $formType)) {
            $mapping = $this->getMetadatas($class)->getAssociationMapping($columnName);

            return array(
                'multiple'      => ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY || $mapping['type'] === ClassMetadataInfo::ONE_TO_MANY),
                'em'            => $this->getObjectManagerName($mapping['targetEntity']),
                'class'         => $mapping['targetEntity'],
                'required'      => $this->isRequired($class, $columnName),
            );
        }

        if (preg_match("#^collection#i", $formType) || preg_match("#collection$#i", $formType)) {
            $mapping = $this->getMetadatas($class)->getAssociationMapping($columnName);

            return array(
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'data_class'    => $mapping['targetEntity']
            );
        }

        return array(
            'required' => $this->isRequired($class, $columnName)
        );
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
        return $this->getMetadatas($class)->getSingleIdentifierFieldName();
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

            return $this->getModelPrimaryKeyName($class);
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
    private function resolveRelatedField($model, $fieldPath)
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
    private function getObjectManagerName($className)
    {
        $doctrine = $this->doctrine;
        $om = $doctrine->getManagerForClass($className);
        foreach ($doctrine->getManagerNames() as $emName=>$omName)
        {
            $instance = $doctrine->getManager($emName);
            if ($instance == $om)
            {
                return $emName;
            }
        }

        throw new \Exception("Entity manager for class: $className not found.");
    }
}
