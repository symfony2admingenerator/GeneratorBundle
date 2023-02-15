<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for custom actions
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ActionsBuilderAction extends ActionsBuilder
{
    public function getOutputName(): string
    {
        return $this->getGenerator()->getGeneratedControllerFolder().'/ActionsController.php';
    }
}
