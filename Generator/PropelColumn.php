<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Doctrine\Inflector\InflectorFactory;

class PropelColumn extends Column
{
    public function getSortOn(): string
    {
        return $this->sortOn != "" ? $this->sortOn : InflectorFactory::create()->build()->classify($this->name);
    }
}
