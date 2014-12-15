<?php

namespace Admingenerator\GeneratorBundle\Validator;

use Admingenerator\GeneratorBundle\Generator\Generator;

interface ValidatorInterface
{
    /**
     * @return void
     */
    public function validate(Generator $generator);
}
