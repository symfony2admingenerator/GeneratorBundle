<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveMakerCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {

        if ($container->hasDefinition('maker.generator')) {
            return;
        }

        // Remove maker command when the maker bundle has not been installed
        $container->removeDefinition('admingenerator.maker.make_admin');
    }
}