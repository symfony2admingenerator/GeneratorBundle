<?php

namespace Admingenerator\GeneratorBundle\Exception;

class InvalidOptionException extends \LogicException
{
    public function __construct($property, $column, $generator, $builder)
    {
        parent::__construct(sprintf(
            'Could not set option "%s" on "%s" column in "%s" %s builder.',
            $property, $column, $generator, $builder
        ));
    }
}
