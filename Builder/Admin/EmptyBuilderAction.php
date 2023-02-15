<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for empty actions
 * @author cedric Lombardot
 */
class EmptyBuilderAction extends BaseBuilder
{
    public function getTemplateDirs(): array
    {
        return [realpath(dirname(__FILE__).'/../../Resources/views/templates')];
    }
}
