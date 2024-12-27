<?php

namespace Admingenerator\GeneratorBundle\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class TestCase extends PHPUnitTestCase
{
    protected function getContainer(): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
        )));
    }
}
