<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for New actions
 * @author cedric Lombardot
 */
class NewBuilderTemplate extends NewBuilder
{
    /**
     * (non-PHPdoc)
     * @see \Admingenerator\GeneratorBundle\Builder\BaseBuilder::getTemplatesToGenerate()
     */
    public function getTemplatesToGenerate(): array
    {
        return parent::getTemplatesToGenerate() + [
            'EditBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'New/index.html.twig',
            'Edit/FormBuilderTemplate'.self::TWIG_EXTENSION
                => 'Resources/views/'.$this->getBaseGeneratorName().'New/form.html.twig',
            ];
    }
}
