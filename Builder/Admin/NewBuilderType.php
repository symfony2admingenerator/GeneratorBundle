<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates form for New actions
 * @author cedric Lombardot
 */
class NewBuilderType extends NewBuilder
{
    public function getOutputName(): string
    {
        return 'Form/Base'.$this->getBaseGeneratorName().'Type/NewType.php';
    }

    public function getTemplateName(): string
    {
        if ($this->environment === null) {
            return $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
        }
        return '@AdmingeneratorGenerator/templates/' . $this->getGenerator()->getTemplateBaseDir() . 'EditBuilderType' . self::TWIG_EXTENSION;
    }
}
