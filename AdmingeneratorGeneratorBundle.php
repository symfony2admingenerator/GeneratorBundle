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
     * @var boolean
     */
    private $classLoaderInitialized = false;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->initAdmingeneratorClassLoader($kernel->getCacheDir());
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ValidatorPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new TwigLoaderPass());
    }

    /**
     * @return \Admingenerator\GeneratorBundle\DependencyInjection\AdmingeneratorGeneratorExtension
     */
    public function getContainerExtension()
    {
        $this->extension = new DependencyInjection\AdmingeneratorGeneratorExtension($this->kernel);

        return $this->extension;
    }

    /**
     * Initialize Admingenerator Class loader
     *
     * @param string $cacheDir
     */
    private function initAdmingeneratorClassLoader($cacheDir)
    {
        if ($this->classLoaderInitialized) {
            return;
        }

        $this->classLoaderInitialized = true;

        $admingeneratedClassLoader = new AdmingeneratedClassLoader();
        $admingeneratedClassLoader->setBasePath($cacheDir);
        $admingeneratedClassLoader->register();
    }
}
