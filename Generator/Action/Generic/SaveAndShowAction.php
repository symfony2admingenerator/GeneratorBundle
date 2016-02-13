<?php

namespace Admingenerator\GeneratorBundle\Generator\Action\Generic;

use Admingenerator\GeneratorBundle\Builder\Admin\BaseBuilder;
use Admingenerator\GeneratorBundle\Generator\Action;

/**
 * This class describes generic Save and Show action
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Bob van de Vijver <bobvandevijver@hotmail.com>
 */
class SaveAndShowAction extends Action
{
    public function __construct($name, BaseBuilder $builder)
    {
        parent::__construct($name, 'generic');

        $this->setSubmit(true);
        $this->setClass('btn-info');
        $this->setIcon('fa-check');
        $this->setLabel('action.generic.save-and-show');
    }
}
