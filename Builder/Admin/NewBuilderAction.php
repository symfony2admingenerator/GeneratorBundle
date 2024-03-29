<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for New actions
 * @author cedric Lombardot
 */
class NewBuilderAction extends NewBuilder
{
    public function getOutputName(): string
    {
        return $this->getGenerator()->getGeneratedControllerFolder().'/NewController.php';
    }
}
