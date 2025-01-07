<?php

namespace Admingenerator\GeneratorBundle\Guesser;

use Admingenerator\GeneratorBundle\Exception\NotImplementedException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\HttpKernel\Kernel;

abstract class DoctrineFieldGuesser implements FieldGuesser
{
    private string $objectModel;

    public function __construct(
        private readonly ManagerRegistry $registry,
        string $objectModel,
        private readonly array $formTypes,
        private readonly array $filterTypes,
        private readonly bool $guessRequired,
        private readonly bool $defaultRequired)
    {
        if (!in_array($objectModel = strtolower($objectModel), array('document', 'entity'))) {
            throw new \InvalidArgumentException('$objectModel must be Document or Entity');
        }

        $this->objectModel = strtolower($objectModel);
    }

    protected function getMetadatas($class): ClassMetadata
    {
        // Cache is implemented by Doctrine itself
        return $this->registry->getManagerForClass($class)->getClassMetadata($class);
    }

    public function getAllFields(string $class): array
    {
        return array_merge($this->getMetadatas($class)->getFieldNames(), $this->getMetadatas($class)->getAssociationNames());
    }

    public function getManyToMany(string $model, string $fieldPath): bool
    {
        $resolved = $this->resolveRelatedField($model, $fieldPath);
        $metadata = $this->getMetadatas($resolved['class']);
        return $metadata->hasAssociation($resolved['field']) && !$metadata->isAssociationWithSingleJoinColumn($resolved['field']);
    }

    public function getDbType(string $model, string $fieldPath): string
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

    public function getModelType(string $model, string $fieldPath): string
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

    public function getSortType(string $dbType): string
    {
        $alphabeticTypes = [
            'string',
            'text',
            'id',
            'custom_id',
        ];

        $numericTypes = [
            'decimal',
            'float',
            'int',
            'integer',
            'int_id',
            'bigint',
            'smallint',
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

    public function getFilterType(string $dbType, string $class, string $columnName): string
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

        if ('boolean' == $dbType && preg_match("/ChoiceType$/i", $type)) {
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

        if ('boolean' == $dbType && preg_match("/CheckboxType/i", $type)) {
            return [
                'required' => false,
            ];
        }

        if (preg_match("/NumberType/i", $type)) {
            $mapping = $this->getMetadatas($class)->getFieldMapping($columnName);

            if (isset($mapping['scale'])) {
                $scale = $mapping['scale'];
            }

            if (isset($mapping['precision'])) {
                $scale = $mapping['precision'];
            }

            return [
                'scale' => $scale ?? null,
                'required'  => !$filter && $this->isRequired($class, $columnName)
            ];
        }

        if (preg_match(sprintf('/%sType$/i', ucfirst($this->objectModel)), $type)) {
            $mapping = $this->getMetadatas($class)->getAssociationMapping($columnName);

            return array(
                'multiple'      => ($mapping['type'] === ORMClassMetadata::MANY_TO_MANY || $mapping['type'] === ORMClassMetadata::ONE_TO_MANY),
                'em'            => $this->getObjectManagerName($mapping['target'.ucfirst($this->objectModel)]),
                'class'         => $mapping['target'.ucfirst($this->objectModel)],
                'required'      => !$filter && $this->isRequired($class, $columnName),
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
                $options['entry_options'] = [
                    'class' => $mapping['target'.ucfirst($this->objectModel)]
                ];
            }

            return $options;
        }

        return array(
            'required' => !$filter && $this->isRequired($class, $columnName)
        );
    }

    protected function isRequired(string $class, string $fieldName): bool
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

    public function getModelPrimaryKeyName(string $class): ?string
    {
        $identifierFieldNames = $this->getMetadatas($class)->getIdentifierFieldNames();

        return !empty($identifierFieldNames) ? $identifierFieldNames[0] : null;
    }

    public function getPrimaryKeyFor(string $model, string $fieldPath): ?string
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
    protected function resolveRelatedField(string $model, string $fieldPath): array
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

        return [
            'field' => $field,
            'class' => $class
        ];
    }

    /**
     * Retrieve Doctrine EntityManager name for class $className
     *
     * @throws \Exception
     */
    protected function getObjectManagerName(string $className): string
    {
        $om = $this->registry->getManagerForClass($className);
        foreach ($this->registry->getManagerNames() as $emName => $omName)
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
