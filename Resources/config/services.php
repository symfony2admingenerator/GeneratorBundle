<?php

use Admingenerator\GeneratorBundle\CacheBuilder\GeneratorCacheBuilder;
use Admingenerator\GeneratorBundle\CacheWarmer\GeneratorCacheWarmer;
use Admingenerator\GeneratorBundle\Command\AssetsInstallCommand;
use Admingenerator\GeneratorBundle\Command\GenerateBaseClassesCommand;
use Admingenerator\GeneratorBundle\EventListener\ControllerListener;
use Admingenerator\GeneratorBundle\Maker\MakeAdmin;
use Admingenerator\GeneratorBundle\Pagerfanta\View\AdmingeneratorView;
use Admingenerator\GeneratorBundle\Routing\NestedRoutingLoader;
use Admingenerator\GeneratorBundle\Routing\RoutingLoader;
use Admingenerator\GeneratorBundle\Twig\Extension\ArrayExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\ClassifyExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\ConfigExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\CsrfTokenExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\ExtendsAdmingeneratedExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\LocalizedMoneyExtension;
use Admingenerator\GeneratorBundle\Twig\Extension\SecurityExtension;
use Admingenerator\GeneratorBundle\Validator\ModelClassValidator;
use Admingenerator\GeneratorBundle\Validator\PropelModelClassValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use TwigGenerator\Extension\ExtraFilterExtension;
use TwigGenerator\Extension\PHPPrintExtension;
use TwigGenerator\Extension\TwigPrintExtension;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('routing.loader.admingenerator.class', RoutingLoader::class)
        ->set('routing.loader.admingenerator_nested.class', NestedRoutingLoader::class)
        ->set('admingenerator.generator_cache_builder', GeneratorCacheBuilder::class)
        ->set('admingenerator.cache_warmer.class', GeneratorCacheWarmer::class)
        ->set('form.type_guesser.admingenerator.class', ValidatorTypeGuesser::class)
        ->set('admingenerator.pagination.class', AdmingeneratorView::class)
        ->set('admingenerator.base_admin_template', '@AdmingeneratorGenerator/base.html.twig')
        ->set('admingenerator.overwrite_if_exists', false)
        ->set('admingenerator.validator.model_class.class', ModelClassValidator::class)
        ->set('admingenerator.validator.propel_model_class.class', PropelModelClassValidator::class);

    $services = $container->services();
    $configureTwigExtension = static fn(string $id, string $class) => $services->set($id, $class)->tag('twig.extension');
    $configureTwigExtension('twig.extension.admingenerator.array', ArrayExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.classify', ClassifyExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.echo', EchoExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.extra_filter', ExtraFilterExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.twig_print', TwigPrintExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.php_print', PHPPrintExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.config', ConfigExtension::class)
        ->arg('$bundleConfig', param('admingenerator'));
    $configureTwigExtension('twig.extension.admingenerator.csrf', CsrfTokenExtension::class)
        ->arg('$csrfTokenManager', service('security.csrf.token_manager'));
    $configureTwigExtension('twig.extension.admingenerator.localized_money', LocalizedMoneyExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.extends', ExtendsAdmingeneratedExtension::class);
    $configureTwigExtension('twig.extension.admingenerator.security', SecurityExtension::class)
        ->arg('$authorizationChecker', AuthorizationCheckerInterface::class);

    $services->set('admingenerator.generator.listener', ControllerListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest'])
        ->arg('$container', service('service_container'))
        ->arg('$twig', service('twig'));

    $services->set('routing.loader.admingenerator', param('routing.loader.admingenerator.class'))
        ->tag('routing.loader')
        ->arg('$locator', service('file_locator'));

    $services->set('routing.loader.admingenerator_nested', param('routing.loader.admingenerator_nested.class'))
        ->tag('routing.loader')
        ->arg('$locator', service('file_locator'));

    $services->set('pagerfanta.view.admingenerator', param('admingenerator.pagination.class'))
        ->tag('pagerfanta.view', ['alias' => 'admingenerator'])
        ->arg('$translator', service('translator'));

    $services->set('admingenerator.maker.make_admin', MakeAdmin::class)
        ->tag('maker.command')
        ->tag('container.no_preload');
    $services->set('admingenerator.command.install-assets', AssetsInstallCommand::class)
        ->tag('console.command', ['command' => 'admin:install-assets'])
        ->arg('$projectDir', param('kernel.project_dir'));

    $services->set('admingenerator.validator.model_class', param('admingenerator.validator.model_class.class'))
        ->tag('admingenerator.validator', ['alias' => 'model_class']);

    $services->set('admingenerator.validator.propel_model_class', param('admingenerator.validator.propel_model_class.class'))
        ->tag('admingenerator.validator.propel', ['alias' => 'propel_model_class']);

    $services->set('admingenerator.generator_cache_builder', param('admingenerator.generator_cache_builder'))
        ->arg('$container', service('service_container'));

    $services->set('admingenerator.cache_warmer', param('admingenerator.cache_warmer.class'))
        ->tag('kernel.cache_warmer', ['priority' => 100])
        ->arg('$generatorCacheBuilder', service('admingenerator.generator_cache_builder'));

    $services->set('admingenerator.command.generate_base_classes', GenerateBaseClassesCommand::class)
        ->tag('console.command', ['command' => 'admin:generate-base-classes'])
        ->arg('$generatorCacheBuilder', service('admingenerator.generator_cache_builder'));
};