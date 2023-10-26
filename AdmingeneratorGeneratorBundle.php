<?php

namespace Admingenerator\GeneratorBundle;

use Admingenerator\GeneratorBundle\DependencyInjection\AdmingeneratorGeneratorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\TwigLoaderPass;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\ValidatorPass;
use Admingenerator\GeneratorBundle\DependencyInjection\Compiler\FormPass;

class AdmingeneratorGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ValidatorPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new TwigLoaderPass());
    }

    public function getContainerExtension(): AdmingeneratorGeneratorExtension
    {
        $this->extension = new AdmingeneratorGeneratorExtension();

        return $this->extension;
    }
}
