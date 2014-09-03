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
    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
     */
    public function getYamlKey()
    {
        return 'show';
    }

    /**
     * Return a list of action from list.object_actions
     * @return array
     */
    public function getObjectActions()
    {
        $objectActions = parent::getObjectActions();

        if (array_key_exists('show', $objectActions)) {
            unset($objectActions['show']);
            $this->objectActions = $objectActions;
        }

        return $this->objectActions;
    }
}
