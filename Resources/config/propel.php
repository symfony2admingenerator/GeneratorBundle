<?php

use Admingenerator\GeneratorBundle\Generator\PropelGenerator;
use Admingenerator\GeneratorBundle\Guesser\PropelORMFieldGuesser;
use Admingenerator\GeneratorBundle\QueryFilter\PropelQueryFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('admingenerator.propel.class', PropelGenerator::class)
        ->set('admingenerator.fieldguesser.propel.class', PropelORMFieldGuesser::class)
        ->set('admingenerator.queryfilter.propel.class', PropelQueryFilter::class);

    $services = $container->services();
    $services->set('admingenerator.fieldguesser.propel', param('admingenerator.fieldguesser.propel.class'));

    $services->set('admingenerator.generator.propel', param('admingenerator.propel.class'))
        ->arg('$outputDir', param('kernel.cache_dir'))
        ->call('setBundleConfig', [param('admingenerator')])
        ->call('setFieldGuesser', [service('admingenerator.fieldguesser.propel')])
        ->call('setRouter', [service('router')])
        ->call('setTwig', [service('twig')])
        ->call('setKernel', [service('kernel')])
        ->public();
};