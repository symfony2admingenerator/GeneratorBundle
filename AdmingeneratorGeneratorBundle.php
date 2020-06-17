<?php

namespace Admingenerator\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Admingenerator\GeneratorBundle\ClassLoader\AdmingeneratedClassLoader;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\TwigLoaderPass;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\ValidatorPass;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\FormPass;
use Symfony\Component\HttpKernel\KernelInterface;

class AdmingeneratorGeneratorBundle extends Bundle
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        AdmingeneratedClassLoader::initAdmingeneratorClassLoader($container->getParameter('kernel.cache_dir'));
        $container->addCompilerPass(new ValidatorPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new TwigLoaderPass());
    }

    /**
     * @return \Admingenerator\GeneratorBundle\DependencyInjection\AdmingeneratorGeneratorExtension
     */
    public function getContainerExtension()
    {
        $this->extension = new DependencyInjection\AdmingeneratorGeneratorExtension();

        return $this->extension;
    }
}
