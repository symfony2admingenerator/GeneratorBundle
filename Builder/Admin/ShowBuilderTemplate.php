<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for edit actions
 * @author Eymen Gunay
 */
class ShowBuilderTemplate extends ShowBuilder
{
    public function getOutputName(): string
    {
        return 'Resources/views/'.$this->getBaseGeneratorName().'Show/index.html.twig';
    }
}
