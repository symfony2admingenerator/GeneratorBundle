<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for edit actions
 * @author Eymen Gunay
 */
class ShowBuilderAction extends ShowBuilder
{
    public function getOutputName(): string
    {
        return $this->getGenerator()->getGeneratedControllerFolder().'/ShowController.php';
    }
}
