<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection;

use Admingenerator\GeneratorBundle\Exception\ModelManagerNotSelectedException;
use Admingenerator\GeneratorBundle\Filesystem\GeneratorsFinder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class AdmingeneratorGeneratorExtension extends Extension
{
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
    }

    /**
     * Prepend KnpMenuBundle config
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = array('twig' => array(
            'template' => 'AdmingeneratorGeneratorBundle:KnpMenu:knp_menu_trans.html.twig',
        ));

        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'knp_menu':
                    $container->prependExtensionConfig($name, $config);
                    break;
            }
        }
    }

    public function load(array $configs, ContainerBuilder $container)
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
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @throws ModelManagerNotSelectedException
     */
    private function processModelManagerConfiguration(array $config, ContainerBuilder $container)
    {
        if (!($config['use_doctrine_orm'] || $config['use_doctrine_odm'] || $config['use_propel'] || $config['use_propel2'])) {
            throw new ModelManagerNotSelectedException();
        }

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'config'));
        $config['templates_dirs'][] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'templates';

        $doctrineOrmTemplatesDirs = array();
        $doctrineOdmTemplatesDirs = array();
        $propelTemplatesDirs = array();
        foreach ($config['templates_dirs'] as $dir) {
            $doctrineOrmTemplatesDirs[] = $dir . DIRECTORY_SEPARATOR . 'Doctrine';
            $doctrineOdmTemplatesDirs[] = $dir . DIRECTORY_SEPARATOR . 'DoctrineODM';
            $propelTemplatesDirs[] = $dir . DIRECTORY_SEPARATOR . 'Propel';
            $propel2TemplatesDirs[] = $dir . DIRECTORY_SEPARATOR . 'Propel2';
        }

        if ($config['use_doctrine_orm']) {
            $loader->load('doctrine_orm.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine'), $doctrineOrmTemplatesDirs);
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
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine_odm'), $doctrineOdmTemplatesDirs);
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
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.propel'), $propelTemplatesDirs);
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


        if ($config['use_propel2']) {
            $loader->load('propel2.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.propel2'), $propel2TemplatesDirs);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.propel2')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $container->getDefinition('admingenerator.fieldguesser.propel2')
                ->addArgument($config['form_types']['propel2'])
                ->addArgument($config['filter_types']['propel2'])
                ->addArgument($config['guess_required'])
                ->addArgument($config['default_required']);
        }
    }

    /**
     * Update $generatorDefinition to add calls to the addTemplatesDirectory
     * for all $directories.
     *
     * @param Definition $generatorDefinition
     * @param array $directories
     */
    private function addTemplatesInitialization(Definition $generatorDefinition, array $directories)
    {
        foreach ($directories as $directory) {
            $generatorDefinition->addMethodCall(
                'addTemplatesDirectory',
                array($directory)
            );
        }
    }

    /**
     * @param array $twigConfiguration
     * @param ContainerBuilder $container
     */
    private function processTwigConfiguration(array $twigConfiguration, ContainerBuilder $container)
    {
        $container->setParameter('admingenerator.twig', $twigConfiguration);

        if ($twigConfiguration['use_localized_date']) {
            // Register Intl extension for localized date
            $container->register('twig.extension.intl', 'Twig_Extensions_Extension_Intl')->addTag('twig.extension');
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function processCacheConfiguration(array $config, ContainerBuilder $container)
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

    /**
     * @param string $cacheProviderServiceName
     * @param Definition $serviceDefinition
     */
    private function addCacheProviderToGenerator($cacheProviderServiceName, Definition $serviceDefinition, ContainerBuilder $container)
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
     * @param ContainerBuilder $container
     */
    private function registerGeneratedFormsAsServices(ContainerBuilder $container)
    {
        $finder = new GeneratorsFinder($this->kernel);

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
     *
     * @param array $generatorParameters
     * @param array $builders
     * @param ContainerBuilder $container
     */
    private function registerFormsServicesFromGenerator(array $generatorParameters, array $builders, ContainerBuilder $container)
    {
        $modelParts = explode('\\', $generatorParameters['model']);
        $model = strtolower(array_pop($modelParts)); // @TODO: BC Support, remove it starting from v.3.0.0
        $fullQualifiedNormalizedModelName = strtolower(str_replace('\\', '_', ltrim($generatorParameters['model'], '\\')));
        $formsBundleNamespace = sprintf(
            '%s%s\\Form\\Type\\%s',
            $generatorParameters['namespace_prefix'] ? $generatorParameters['namespace_prefix'] . '\\' : '',
            $generatorParameters['bundle_name'],
            $generatorParameters['prefix']
        );
        $authorizationCheckerServiceReference = new Reference('security.authorization_checker');

        if (in_array('new', $builders)) {
            $newDefinition = new Definition($formsBundleNamespace . '\\' . 'NewType');
            $newDefinition
                ->addMethodCall('setAuthorizationChecker', array($authorizationCheckerServiceReference))
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedModelName . '_new'), $newDefinition);
            $container->setAlias('admingen_generator_' . $model . '_new', $id);
        }

        if (in_array('edit', $builders)) {
            $editDefinition = new Definition($formsBundleNamespace . '\\' . 'EditType');
            $editDefinition
                ->addMethodCall('setAuthorizationChecker', array($authorizationCheckerServiceReference))
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedModelName . '_edit'), $editDefinition);
            $container->setAlias('admingen_generator_' . $model . '_edit', $id);
        }

        if (in_array('list', $builders) || in_array('nested_list', $builders)) {
            $filterDefinition = new Definition($formsBundleNamespace . '\\' . 'FiltersType');
            $filterDefinition
                ->addMethodCall('setAuthorizationChecker', array($authorizationCheckerServiceReference))
                ->addTag('form.type');

            $container->setDefinition(($id = 'admingen_generator_' . $fullQualifiedNormalizedModelName . '_filter'), $filterDefinition);
            $container->setAlias('admingen_generator_' . $model . '_filter', $id);
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'admingenerator_generator';
    }
}
