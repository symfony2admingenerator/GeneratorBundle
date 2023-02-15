<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for lists actions.
 *
 * @author cedric Lombardot
 */
class ListBuilderTemplate extends ListBuilder
{
    protected ?string $filtersMode = null;

    protected ?string $defaultFiltersVisibility = null;

    protected ?string $filtersModalSize = null;

    /**
     * Retrieve the filters mode parameter defined into the
     * YAML generator file under the list builder.
     */
    public function getFiltersMode(): string
    {
        if (null === $this->filtersMode) {
            $this->filtersMode = $this->getGenerator()->getFromYaml('builders.list.params.filtersMode', 'default');
        }

        return $this->filtersMode;
    }

    public function getDefaultFiltersVisibility(): string
    {
        if (null === $this->defaultFiltersVisibility) {
            $this->defaultFiltersVisibility = $this->getGenerator()->getFromYaml('builders.list.params.defaultFiltersVisibility', 'expanded');
        }

        return $this->defaultFiltersVisibility;
    }

    public function getFiltersModalSize(): string
    {
        if (null === $this->filtersModalSize) {
            $this->filtersModalSize = $this->getGenerator()->getFromYaml('builders.list.params.filtersModalSize', 'medium');
        }

        return $this->filtersModalSize;
    }

    public function getTemplatesToGenerate(): array
    {
        return parent::getTemplatesToGenerate() + [
            'ListBuilderTemplate'.self::TWIG_EXTENSION => 'Resources/views/'.$this->getBaseGeneratorName().'List/index.html.twig',
            'List/FiltersBuilderTemplate'.self::TWIG_EXTENSION => 'Resources/views/'.$this->getBaseGeneratorName().'List/filters.html.twig',
            'List/ResultsBuilderTemplate'.self::TWIG_EXTENSION => 'Resources/views/'.$this->getBaseGeneratorName().'List/results.html.twig',
            'List/RowBuilderTemplate'.self::TWIG_EXTENSION => 'Resources/views/'.$this->getBaseGeneratorName().'List/row.html.twig',
            ];
    }
}
