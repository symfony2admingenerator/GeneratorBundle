<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for lists actions
 * @author cedric Lombardot
 */
class NestedListBuilderAction extends NestedListBuilder
{
    public function getOutputName(): string
    {
        return $this->getGenerator()->getGeneratedControllerFolder().'/ListController.php';
    }
}
