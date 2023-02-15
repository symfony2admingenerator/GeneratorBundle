<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for edit actions
 * @author cedric Lombardot
 */
class EditBuilderTemplate extends EditBuilder
{
    public function getTemplatesToGenerate(): array
    {
        return parent::getTemplatesToGenerate() + [
                'EditBuilderTemplate'.self::TWIG_EXTENSION
                    => 'Resources/views/'.$this->getBaseGeneratorName().'Edit/index.html.twig',
                'Edit/FormBuilderTemplate'.self::TWIG_EXTENSION
                    => 'Resources/views/'.$this->getBaseGeneratorName().'Edit/form.html.twig',
            ];
    }
}
