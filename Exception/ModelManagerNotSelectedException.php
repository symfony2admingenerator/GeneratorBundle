<?php

namespace Admingenerator\GeneratorBundle\Exception;

use LogicException;

class ModelManagerNotSelectedException extends LogicException
{
    public function __construct()
    {
        parent::__construct("You must enable one model manager in Admingenerator config.");
    }
}
