<?php

use Admingenerator\GeneratorBundle\Generator\DoctrineODMGenerator;
use Admingenerator\GeneratorBundle\Guesser\DoctrineODMFieldGuesser;
use Admingenerator\GeneratorBundle\QueryFilter\DoctrineODMQueryFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('admingenerator.doctrine_odm.class', DoctrineODMGenerator::class)
        ->set('admingenerator.fieldguesser.doctrine_odm.class', DoctrineODMFieldGuesser::class)
        ->set('admingenerator.queryfilter.doctrine_odm.class', DoctrineODMQueryFilter::class);

    $services = $container->services();
    $services->set('admingenerator.fieldguesser.doctrine_odm', param('admingenerator.fieldguesser.doctrine_odm.class'))
        ->arg('$registry', service('doctrine'))
        ->arg('$objectModel', 'document');

    $services->set('admingenerator.generator.doctrine_odm', param('admingenerator.doctrine_odm.class'))
        ->arg('$outputDir', param('kernel.cache_dir'))
        ->call('setBundleConfig', [param('admingenerator')])
        ->call('setFieldGuesser', [service('admingenerator.fieldguesser.doctrine_odm')])
        ->call('setRouter', [service('router')])
        ->call('setTwig', [service('twig')])
        ->call('setKernel', [service('kernel')])
        ->public();
};
