<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates form for Filters
 * @author cedric Lombardot
 */
class FiltersBuilderType extends ListBuilder
{
    public function getOutputName()
    {
        return 'Form/Base'.$this->getBaseGeneratorName().'Type/FiltersType.php';
    }

    public function getTemplateName()
    {
        if ($this->environment === null) {
            return $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
        }
        return '@AdmingeneratorGenerator/templates/' . $this->getGenerator()->getTemplateBaseDir() . 'FiltersBuilderType' . self::TWIG_EXTENSION;
    }
}
