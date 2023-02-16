<?php

namespace Admingenerator\GeneratorBundle\Generator\Action\Object;

use Admingenerator\GeneratorBundle\Builder\Admin\BaseBuilder;
use Admingenerator\GeneratorBundle\Generator\Action;

/**
 * This class describes object Delete action
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class DeleteAction extends Action
{
    public function __construct($name, BaseBuilder $builder)
    {
        parent::__construct($name, 'object');

        $this->setIcon('fa-times');
        $this->setLabel('action.object.delete.label');
        $this->setConfirm('action.object.delete.confirm');
        $this->setCsrfProtected(true);

        $this->setRoute($builder->getObjectActionsRoute());

        $this->setParams([
            'pk' => '{{ '.$builder->getModelClass().'.'.$builder->getModelPrimaryKeyName().' }}',
            'action' => 'delete'
        ]);

    }
}
