<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for edit actions
 * @author cedric Lombardot
 */
class EditBuilderAction extends EditBuilder
{
    public function getOutputName(): string
    {
        return $this->getGenerator()->getGeneratedControllerFolder().'/EditController.php';
    }

    /**
     * Return a list of action from list.object_actions
     */
    public function getObjectActions(): array
    {
        $objectActions = parent::getObjectActions();

        if (array_key_exists('edit', $objectActions)) {
            unset($objectActions['edit']);
            $this->objectActions = $objectActions;
        }

        return $this->objectActions;
    }
}
