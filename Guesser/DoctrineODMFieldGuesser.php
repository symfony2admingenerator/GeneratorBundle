<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerAware;

class DoctrineODMFieldGuesser extends ContainerAware
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var boolean
     */
    private $guessRequired;

    /**
     * @var boolean
     */
    private $defaultRequired;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    protected function getMetadatas($class)
    {
        return $this->documentManager->getClassMetadata($class);
    }

    public function getAllFields($class)
    {
        return array_merge($this->getMetadatas($class)->getFieldNames(), $this->getMetadatas($class)->getAssociationNames());
    }

    public function getManyToMany($model, $fieldPath)
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        return !$this->getMetadatas($resolved['class'])->isAssociationWithSingleJoinColumn($resolved['field']);
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

        $metadata = $this->getMetadatas($class);

        if ($metadata->hasAssociation($field)) {
            if ($metadata->isSingleValuedAssociation($field)) {
                return 'document';
            }

            return 'collection';
        }

        if ($this->getMetadatas($class)->hasField($field)) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($field);

            return $mapping['type'];
        }

        return 'virtual';
    }

    public function getModelType($class, $fieldName)
    {
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
            'id',
            'custom_id',
            'string',
            'text',
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

    public function getFormType($dbType, $class, $columnName)
    {
        $formTypes = $this->container->getParameter('admingenerator.doctrineodm_form_types');

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
        $filterTypes = $this->container->getParameter('admingenerator.doctrineodm_filter_types');

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

        if ('boolean' == $dbType &&
            (preg_match("#^choice#i", $type) || preg_match("#choice$#i", $type))) {
            return array(
                'choices' => array(
                   0 => 'boolean.no',
                   1 => 'boolean.yes'
                ),
                'empty_value' => 'boolean.yes_or_no',
                'translation_domain' => 'Admingenerator'
            );
        }
        
        if ('boolean' == $dbType &&
            (preg_match("#^checkbox#i", $type) || preg_match("#checkbox#i", $type))) {
            return array(
                'required' => false,
            );
        }

        if (preg_match("#^document#i", $type) || preg_match("#document$#i", $type)) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            return array(
                'class'         => $mapping['targetDocument'],
                'multiple'      => false,
            );
        }

        if (preg_match("#^collection#i", $type) || preg_match("#collection$#i", $type)) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            return array(
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'type' => 'entity',
                'options' => array(
                    'class' => $mapping['targetEntity']
                )
            );
        }

        // TODO: is this still needed? is this valid?
        if ('collection' === $dbType) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            return array(
                'class' => isset($mapping['targetDocument']) ? $mapping['targetDocument'] : null
            );
        }

        return array('required' => $filter ? false : $this->isRequired($class, $columnName));
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
     * Find the pk name
     *
     * @param $class
     * @return string
     */
    public function getModelPrimaryKeyName($class)
    {
        return $this->getMetadatas($class)->getIdentifier();
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
}
