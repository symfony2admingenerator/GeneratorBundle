<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for empty actions
 * @author cedric Lombardot
 */
class EmptyBuilderAction extends BaseBuilder
{
    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BuilderInterface::getDefaultTemplateDirs()
     */
    public function getTemplateDirs()
    {
        $reflClass = new \ReflectionClass($this);

        return array(realpath(dirname($reflClass->getFileName().'/../../Resources/templates'));
    }
}
