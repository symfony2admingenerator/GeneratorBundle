<?php

use Admingenerator\GeneratorBundle\Generator\DoctrineGenerator;
use Admingenerator\GeneratorBundle\Guesser\DoctrineFieldGuesser;
use Admingenerator\GeneratorBundle\QueryFilter\DoctrineQueryFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('admingenerator.doctrine.class', DoctrineGenerator::class)
        ->set('admingenerator.fieldguesser.doctrine.class', DoctrineFieldGuesser::class)
        ->set('admingenerator.queryfilter.doctrine.class', DoctrineQueryFilter::class);

    $services = $container->services();
    $services->set('admingenerator.fieldguesser.doctrine', param('admingenerator.fieldguesser.doctrine.class'))
        ->arg('$registry', service('doctrine'))
        ->arg('$objectModel', 'entity');

    $services->set('admingenerator.generator.doctrine', param('admingenerator.doctrine.class'))
        ->arg('$outputDir', param('kernel.cache_dir'))
        ->call('setBundleConfig', [param('admingenerator')])
        ->call('setFieldGuesser', [service('admingenerator.fieldguesser.doctrine')])
        ->call('setRouter', [service('router')])
        ->call('setTwig', [service('twig')])
        ->call('setKernel', [service('kernel')])
        ->public();
};