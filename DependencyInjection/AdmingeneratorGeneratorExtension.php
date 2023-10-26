<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection;

use Admingenerator\GeneratorBundle\CacheBuilder\GeneratorCacheBuilder;
use Admingenerator\GeneratorBundle\Exception\ModelManagerNotSelectedException;
use Admingenerator\GeneratorBundle\Filesystem\GeneratorsFinder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class AdmingeneratorGeneratorExtension extends Extension
{
    /**
     * Prepend KnpMenuBundle config
     */
    public function prepend(ContainerBuilder $container): void
    {
        $knpConfig = ['twig' => [
            'template' => 'AdmingeneratorGeneratorBundle:KnpMenu:knp_menu_trans.html.twig',
        ]];

        foreach ($container->getExtensions() as $name => $extension) {
            if ($name == 'knp_menu') {
                $container->prependExtensionConfig($name, $knpConfig);
            }
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('admingenerator', $config);
        $container->setParameter('admingenerator.base_admin_template', $config['base_admin_template']);
        $container->setParameter('admingenerator.dashboard_route', $config['dashboard_route']);
        $container->setParameter('admingenerator.login_route', $config['login_route']);
        $container->setParameter('admingenerator.logout_route', $config['logout_route']);
        $container->setParameter('admingenerator.exit_route', $config['exit_route']);
        $container->setParameter('admingenerator.stylesheets', $config['stylesheets']);
        $container->setParameter('admingenerator.javascripts', $config['javascripts']);
        $container->setParameter('admingenerator.default_action_after_save', $config['default_action_after_save']);

        $container->setParameter('admingenerator.throw_exceptions', $container->getParameter('kernel.debug')
            ? true
            : $config['throw_exceptions']
        );

        $container->setParameter('admingenerator.use_doctrine_orm_batch_remove', $config['use_doctrine_orm_batch_remove']);
        $container->setParameter('admingenerator.use_doctrine_odm_batch_remove', $config['use_doctrine_odm_batch_remove']);
        $container->setParameter('admingenerator.use_propel_batch_remove', $config['use_propel_batch_remove']);

        if ($config['use_jms_security']) {
            $container->getDefinition('twig.extension.admingenerator.security')->addArgument(true);
            $container->getDefinition('twig.extension.admingenerator.echo')->addArgument(true);
        }

        $this->registerGeneratedFormsAsServices($container);
        $this->processModelManagerConfiguration($config, $container);
        $this->processTwigConfiguration($config['twig'], $container);
        $this->processCacheConfiguration($config, $container);

        if ($config['generate_base_in_project_dir']) {
            $container->removeDefinition('admingenerator.cache_warmer');
            $generationDir = $container->getParameterBag()->resolveValue(
                param('kernel.project_dir') . DIRECTORY_SEPARATOR . $config['generate_base_in_project_dir_directory']
            );
            $container->setParameter('admingenerator.generate_base_in_project_dir_directory', $generationDir);
            if ($config['use_doctrine_orm']) {
                $this->replaceGenerationDir($container, 'admingenerator.generator.doctrine');
            }
            if ($config['use_doctrine_odm']) {
                $this->replaceGenerationDir($container, 'admingenerator.generator.doctrine_odm');
            }
            if ($config['use_propel']) {
                $this->replaceGenerationDir($container, 'admingenerator.generator.propel');
            }

            (new GeneratorCacheBuilder($container))->buildEmpty($generationDir, true);
        } else {
            $container->removeDefinition('admingenerator.command.generate_base_classes');
        }
    }

    private function replaceGenerationDir(ContainerBuilder $container, string $definition): void
    {
        $container
            ->getDefinition($definition)
            ->replaceArgument(0, param('admingenerator.generate_base_in_project_dir_directory')->__toString());
    }

    /**
     * @throws ModelManagerNotSelectedException
     */
    private function processModelManagerConfiguration(array $config, ContainerBuilder $container): void
    {
        if (!($config['use_doctrine_orm'] || $config['use_doctrine_odm'] || $config['use_propel'])) {
            throw new ModelManagerNotSelectedException();
        }

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'config'));
        $config['templates_dirs'][] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates';

        if ($config['use_doctrine_orm']) {
            $loader->load('doctrine_orm.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine'), $config['templates_dirs']);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.doctrine')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $container->getDefinition('admingenerator.fieldguesser.doctrine')
                ->addArgument($config['form_types']['doctrine_orm'])
                ->addArgument($config['filter_types']['doctrine_orm'])
                ->addArgument($config['guess_required'])
                ->addArgument($config['default_required']);
        }

        if ($config['use_doctrine_odm']) {
            $loader->load('doctrine_odm.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine_odm'), $config['templates_dirs']);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.doctrine_odm')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $container->getDefinition('admingenerator.generator.doctrine_odm')
                ->addArgument($config['form_types']['doctrine_odm'])
                ->addArgument($config['filter_types']['doctrine_odm'])
                ->addArgument($config['guess_required'])
                ->addArgument($config['default_required']);
        }

        if ($config['use_propel']) {
            $loader->load('propel.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.propel'), $config['templates_dirs']);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.propel')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $container->getDefinition('admingenerator.fieldguesser.propel')
                ->addArgument($config['form_types']['propel'])
                ->addArgument($config['filter_types']['propel'])
                ->addArgument($config['guess_required'])
                ->addArgument($config['default_required']);
        }
    }

    /**
     * Update $generatorDefinition to add calls to the addTemplatesDirectory
     * for all $directories.
     */
    private function addTemplatesInitialization(Definition $generatorDefinition, array $directories): void
    {
        foreach ($directories as $directory) {
            $generatorDefinition->addMethodCall(
                'addTemplatesDirectory',
                [$directory]
            );
        }
    }

    private function processTwigConfiguration(array $twigConfiguration, ContainerBuilder $container): void
    {
        $container->setParameter('admingenerator.twig', $twigConfiguration);

        if ($twigConfiguration['use_localized_date'] && !class_exists('\\Twig\\Extra\\Intl\\IntlExtension')) {
            throw new InvalidArgumentException('Install the twig/intl-extra package to use localized dates!');
        }
    }

    private function processCacheConfiguration(array $config, ContainerBuilder $container): void
    {
        if (empty($config['generator_cache'])) {
            return;
        }

        $container
            ->getDefinition('admingenerator.generator.listener')
            ->addMethodCall('setCacheProvider', array(
                new Reference($config['generator_cache']),
                $container->getParameter('kernel.environment'),
            ));

        if ($config['use_doctrine_orm']) {
            $this->addCacheProviderToGenerator($config['generator_cache'], $container->getDefinition('admingenerator.generator.doctrine'), $container);
        }

        if ($config['use_doctrine_odm']) {
            $this->addCacheProviderToGenerator($config['generator_cache'], $container->getDefinition('admingenerator.generator.doctrine_odm'), $container);
        }

        if ($config['use_propel']) {
            $this->addCacheProviderToGenerator($config['generator_cache'], $container->getDefinition('admingenerator.generator.propel'), $container);
        }
    }

    private function addCacheProviderToGenerator($cacheProviderServiceName, Definition $serviceDefinition, ContainerBuilder $container): void
    {
        $serviceDefinition
            ->addMethodCall('setCacheProvider', array(
                new Reference($cacheProviderServiceName),
                $container->getParameter('kernel.environment'),
            ));
    }

    /**
     * Since Symfony 2.8, we need to use FQCN for FormType (using FormType instance in Factory is deprecated)
     * Unfortunately, our generated form types require Dependency Injection.
     * We so need to register all generated form as services so Security Authorization
     * Checker is injected.
     */
    private function registerGeneratedFormsAsServices(ContainerBuilder $container): void
    {
        $finder = new GeneratorsFinder($container->getParameter('kernel.project_dir'));

        foreach ($finder->findAll() as $path => $generator) {
            $generator = Yaml::parse(file_get_contents($generator));
            if (!array_key_exists('params', $generator)) {
                throw new \InvalidArgumentException('"params" field is missing in ' . $generator);
            }

            if (!array_key_exists('builders', $generator)) {
                throw new \InvalidArgumentException('"builders" field is missing in ' . $generator);
            }
            preg_match('/[^\/|\\\\]*-generator.yml/', $path, $prefix);
            $prefix = substr($prefix[0], 0, strlen($prefix[0]) - strlen('-generator.yml'));
            $generator['params']['prefix'] = $prefix;
            $this->registerFormsServicesFromGenerator($generator['params'], array_keys($generator['builders']), $container);
        }
    }

    /**
     * Register forms as services for a generator.
     * Register only generated forms based on defined builders.
     */
    private function registerFormsServicesFromGenerator(array $generatorParameters, array $builders, ContainerBuilder $container): void
    {
        $modelParts                       = explode('\\', $generatorParameters['model']);
        $model                            = strtolower(array_pop($modelParts)); // @TODO: BC Support, remove it starting from v.3.0.0
        $fullQualifiedNormalizedModelName = strtolower(str_replace('\\', '_', ltrim($generatorParameters['model'], '\\'))); // @TODO: BC Support, remove it starting from v.3.0.0
        $namespace_prefix                 = $generatorParameters['namespace_prefix'] ? $generatorParameters['namespace_prefix'] . '\\' : '';
        $fullQualifiedNormalizedFormName  = sprintf('%s%s_%s', str_replace('\\', '_', ltrim($namespace_prefix, '\\')), $generatorParameters['bundle_name'], $generatorParameters['prefix']);

        $formsBundleNamespace = sprintf(
            '%s%s\\Form\\Type\\%s',
            $namespace_prefix,
            $generatorParameters['bundle_name'],
            $generatorParameters['prefix']
        );
        $authorizationCheckerServiceReference = new Reference('security.authorization_checker');

        if (in_array('new', $builders)) {
            $newDefinition = new Definition($formsBundleNamespace . '\\' . 'NewType');
            $newDefinition
                ->addMethodCall('setAuthorizationChecker', [$authorizationCheckerServiceReference])
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedFormName . '_new'), $newDefinition);
            $container->setAlias('admingen_generator_' . $fullQualifiedNormalizedModelName . '_new', $id);
            $container->setAlias('admingen_generator_' . $model . '_new', $id);
        }

        if (in_array('edit', $builders)) {
            $editDefinition = new Definition($formsBundleNamespace . '\\' . 'EditType');
            $editDefinition
                ->addMethodCall('setAuthorizationChecker', [$authorizationCheckerServiceReference])
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedFormName . '_edit'), $editDefinition);
            $container->setAlias('admingen_generator_' . $fullQualifiedNormalizedModelName . '_edit', $id);
            $container->setAlias('admingen_generator_' . $model . '_edit', $id);
        }

        if (in_array('list', $builders) || in_array('nested_list', $builders)) {
            $filterDefinition = new Definition($formsBundleNamespace . '\\' . 'FiltersType');
            $filterDefinition
                ->addMethodCall('setAuthorizationChecker', [$authorizationCheckerServiceReference])
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedFormName . '_filter'), $filterDefinition);
            $container->setAlias('admingen_generator_' . $fullQualifiedNormalizedModelName . '_filter', $id);
            $container->setAlias('admingen_generator_' . $model . '_filter', $id);
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($this->getAlias());
    }

    public function getAlias(): string
    {
        return 'admingenerator_generator';
    }
}
