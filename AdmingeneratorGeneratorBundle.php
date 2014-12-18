<?php

namespace Admingenerator\GeneratorBundle;

use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\TwigLoaderCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Admingenerator\GeneratorBundle\ClassLoader\AdmingeneratedClassLoader;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\ValidatorCompilerPass;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\FormCompilerPass;

class AdmingeneratorGeneratorBundle extends Bundle
{
    /**
     * @var boolean
     */
    private $classLoaderInitialized = false;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::boot()
     */
    public function boot()
    {
        $this->initAdmingeneratorClassLoader($this->container);
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        $this->initAdmingeneratorClassLoader($container);

        parent::build($container);

        $container->addCompilerPass(new ValidatorCompilerPass());
        $container->addCompilerPass(new FormCompilerPass());
        $container->addCompilerPass(new TwigLoaderCompilerPass());
    }

    /**
     * @return \Admingenerator\GeneratorBundle\DependencyInjection\AdmingeneratorGeneratorExtension
     */
    public function getContainerExtension()
    {
        $this->extension = new DependencyInjection\AdmingeneratorGeneratorExtension();

        return $this->extension;
    }

    /**
     * Initialize Admingenerator Class loader
     *
     * @param ContainerBuilder $container
     */
    private function initAdmingeneratorClassLoader(ContainerInterface $container)
    {
        if (!$this->classLoaderInitialized) {
            $this->classLoaderInitialized = true;

            $AdmingeneratedClassLoader = new AdmingeneratedClassLoader();
            $AdmingeneratedClassLoader->setBasePath($container->getParameter('kernel.cache_dir'));
            $AdmingeneratedClassLoader->register();
        }
    }
}
