<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for show actions
 *
 * @author Eymen Gunay
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ShowBuilder extends BaseBuilder
{
    public function getYamlKey(): string
    {
        return 'show';
    }

    public function getObjectActions(): array
    {
        $objectActions = parent::getObjectActions();

        if (array_key_exists('show', $objectActions)) {
            unset($objectActions['show']);
            $this->objectActions = $objectActions;
        }

        return $this->objectActions;
    }
}
