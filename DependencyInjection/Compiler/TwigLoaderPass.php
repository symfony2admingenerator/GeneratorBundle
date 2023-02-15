<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TwigLoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('twig.loader.filesystem')) {
            $container
                ->getDefinition('twig.loader.filesystem')
                ->addMethodCall('addPath', [$container->getParameter('kernel.cache_dir')]);
        }

        if ($container->hasDefinition('twig.loader.native_filesystem')) {
            $container
                ->getDefinition('twig.loader.native_filesystem')
                ->addMethodCall('addPath', [$container->getParameter('kernel.cache_dir')]);
        }
    }
}
