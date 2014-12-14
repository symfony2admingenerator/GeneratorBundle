<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for lists actions
 * @author cedric Lombardot
 */
class ListBuilderTemplate extends ListBuilder
{
    /**
     * @var string
     */
    protected $filtersMode = null;

    /**
     * Retrieve the filters mode parameter defined into the
     * YAML generator file under the list builder
     *
     * @return string
     */
    public function getFiltersMode()
    {
        if (null === $this->filtersMode) {
            $this->filtersMode = $this->getGenerator()->getFromYaml('builders.list.params.filtersMode', 'default');
        }

        return $this->filtersMode;
    }

    /**
     * (non-PHPdoc)
     * @see \Admingenerator\GeneratorBundle\Builder\BaseBuilder::getTemplatesToGenerate()
     */
    public function getTemplatesToGenerate()
    {
        return parent::getTemplatesToGenerate() + array(
            'ListBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'List/index.html.twig',
            'List/FiltersBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'List/filters.html.twig',
            'List/ResultsBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'List/results.html.twig',
            'List/RowBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'List/row.html.twig',
        );
    }
}
