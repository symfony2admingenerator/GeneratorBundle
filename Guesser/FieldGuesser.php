<?php

namespace Admingenerator\GeneratorBundle\Guesser;


interface FieldGuesser
{
    public function getAllFields(string $class): array;

    public function getManyToMany(string $model, string $fieldPath): bool;

    /**
     * Find out the database type for given model field path.
     *
     * @param string $model The starting model.
     * @param string $fieldPath The field path.
     * @return string The leaf field's primary key.
     */
    public function getDbType(string $model, string $fieldPath): string;

    public function getSortType(string $dbType): string;

    public function getFormType(string $dbType, string $class, string $columnName): string;

    public function getFilterType(string $dbType, string $class, string $columnName): string;

    public function getFormOptions(string $formType, string $dbType, string $model, string $fieldPath): array;

    public function getFilterOptions(string $filterType, string $dbType, string $model, string $fieldPath): array;

    public function getModelPrimaryKeyName(string $class): ?string;

    /**
     * Find out the primary key for given model field path.
     *
     * @param string $model The starting model.
     * @param string $fieldPath The field path.
     * @return string The leaf field's primary key.
     */
    public function getPrimaryKeyFor(string $model, string $fieldPath): ?string;


}