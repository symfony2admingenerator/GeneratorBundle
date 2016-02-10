<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TwigLoaderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('twig.loader.filesystem')) {
            $container
                ->getDefinition('twig.loader.filesystem')
                ->addMethodCall('addPath', array($container->getParameter('kernel.cache_dir')));
        }
    }
}
