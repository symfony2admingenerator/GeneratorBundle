<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Admingenerator\GeneratorBundle\Exception\ModelManagerNotSelectedException;

class AdmingeneratorGeneratorExtension extends Extension implements PrependExtensionInterface
{
  private $defaultFormTypes = array(
      'doctrine_orm' => array(
          'datetime' => 'datetime',
          'vardatetime' => 'datetime',
          'datetimetz' => 'datetime',
          'date' => 'date',
          'time' => 'time',
          'decimal' => 'number',
          'float' => 'number',
          'integer' => 'integer',
          'bigint' => 'integer',
          'smallint' => 'integer',
          'string' => 'text',
          'text' => 'textarea',
          'entity' => 'entity',
          'collection' => 'collection',
          'array' => 'collection',
          'boolean' => 'checkbox'),
      'doctrine_odm' => array(
          'datetime' => 'datetime',
          'timestamp' => 'datetime',
          'vardatetime' => 'datetime',
          'datetimetz' => 'datetime',
          'date' => 'date',
          'time' => 'time',
          'decimal' => 'number',
          'float' => 'number',
          'int' => 'integer',
          'integer' => 'integer',
          'int_id' => 'integer',
          'bigint' => 'integer',
          'smallint' => 'integer',
          'id' => 'text',
          'custom_id' => 'text',
          'string' => 'text',
          'text' => 'textarea',
          'document' => 'document',
          'collection' => 'collection',
          'hash' => 'collection',
          'boolean' => 'checkbox'),
      'propel' => array(
          'TIMESTAMP' => 'datetime',
          'BU_TIMESTAMP' => 'datetime',
          'DATE' => 'date',
          'BU_DATE' => 'date',
          'TIME' => 'time',
          'FLOAT' => 'number',
          'REAL' => 'number',
          'DOUBLE' => 'number',
          'DECIMAL' => 'number',
          'TINYINT' => 'integer',
          'SMALLINT' => 'integer',
          'INTEGER' => 'integer',
          'BIGINT' => 'integer',
          'NUMERIC' => 'integer',
          'CHAR' => 'text',
          'VARCHAR' => 'text',
          'LONGVARCHAR' => 'textarea',
          'BLOB' => 'textarea',
          'CLOB' => 'textarea',
          'CLOB_EMU' => 'textarea',
          'model' => 'model',
          'collection' => 'collection',
          'PHP_ARRAY' => 'collection',
          'ENUM' => 'choice',
          'BOOLEAN' => 'checkbox',
          'BOOLEAN_EMU' => 'checkbox'
      ));

    private $defaultFilterTypes = array(
      'doctrine_orm' => array(
          'datetime' => 'datetime',
          'vardatetime' => 'datetime',
          'datetimetz' => 'datetime',
          'date' => 'date',
          'time' => 'time',
          'decimal' => 'number',
          'float' => 'number',
          'integer' => 'number',
          'bigint' => 'number',
          'smallint' => 'number',
          'string' => 'text',
          'text' => 'text',
          'entity' => 'model',
          'collection' => 'collection',
          'array' => 'text',
          'boolean' => 'choice'),
      'doctrine_odm' => array(
          'datetime' => 'datetime',
          'timestamp' => 'datetime',
          'vardatetime' => 'datetime',
          'datetimetz' => 'datetime',
          'date' => 'date',
          'time' => 'time',
          'decimal' => 'number',
          'float' => 'number',
          'int' => 'number',
          'integer' => 'number',
          'int_id' => 'number',
          'bigint' => 'number',
          'smallint' => 'number',
          'id' => 'text',
          'custom_id' => 'text',
          'string' => 'text',
          'text' => 'text',
          'document' => 'model',
          'collection' => 'collection',
          'hash' => 'text',
          'boolean' => 'choice'),
      'propel' => array(
          'TIMESTAMP' => 'datetime',
          'BU_TIMESTAMP' => 'datetime',
          'DATE' => 'date',
          'BU_DATE' => 'date',
          'TIME' => 'time',
          'FLOAT' => 'number',
          'REAL' => 'number',
          'DOUBLE' => 'number',
          'DECIMAL' => 'number',
          'TINYINT' => 'number',
          'SMALLINT' => 'number',
          'INTEGER' => 'number',
          'BIGINT' => 'number',
          'NUMERIC' => 'number',
          'CHAR' => 'text',
          'VARCHAR' => 'text',
          'LONGVARCHAR' => 'text',
          'BLOB' => 'text',
          'CLOB' => 'text',
          'CLOB_EMU' => 'text',
          'model' => 'model',
          'collection' => 'collection',
          'PHP_ARRAY' => 'text',
          'ENUM' => 'text',
          'BOOLEAN' => 'choice',
          'BOOLEAN_EMU' => 'choice'
      ));
    /**
     * Prepend KnpMenuBundle config
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = array('twig' => array(
            'template' => 'AdmingeneratorGeneratorBundle:KnpMenu:knp_menu_trans.html.twig'
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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setParameter('admingenerator.base_admin_template', $config['base_admin_template']);
        $container->setParameter('admingenerator.dashboard_route', $config['dashboard_route']);
        $container->setParameter('admingenerator.guess_required', $config['guess_required']);
        $container->setParameter('admingenerator.default_required', $config['default_required']);
        $container->setParameter('admingenerator.login_route', $config['login_route']);
        $container->setParameter('admingenerator.logout_route', $config['logout_route']);
        $container->setParameter('admingenerator.exit_route', $config['exit_route']);
        $container->setParameter('admingenerator.stylesheets', $config['stylesheets']);
        $container->setParameter('admingenerator.javascripts', $config['javascripts']);
        $container->setParameter('admingenerator.default_action_after_save', $config['default_action_after_save']);

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
        if (!($config['use_doctrine_orm'] || $config['use_doctrine_odm'] || $config['use_propel'])) {
            throw new ModelManagerNotSelectedException();
        }

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'config'));
        $config['templates_dirs'][] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'templates';

        $doctrineOrmTemplatesDirs = array();
        $doctrineOdmTemplatesDirs = array() ;
        $propelTemplatesDirs = array();
        foreach ($config['templates_dirs'] as $dir) {
            $doctrineOrmTemplatesDirs[] = $dir.DIRECTORY_SEPARATOR.'Doctrine';
            $doctrineOdmTemplatesDirs[] = $dir.DIRECTORY_SEPARATOR.'DoctrineODM';
            $propelTemplatesDirs[]      = $dir.DIRECTORY_SEPARATOR.'Propel';
        }


        if ($config['use_doctrine_orm']) {
            $loader->load('doctrine_orm.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine'), $doctrineOrmTemplatesDirs);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.doctrine')
                    ->addMethodCall('forceOverwriteIfExists');

            }

            $formTypes = array_merge($this->defaultFormTypes['doctrine_orm'], $config['form_types']['doctrine_orm']);
            $filterTypes = array_merge($this->defaultFilterTypes['doctrine_orm'], $config['filter_types']['doctrine_orm']);
            $container->setParameter('admingenerator.doctrine_form_types', $formTypes);
            $container->setParameter('admingenerator.doctrine_filter_types', $filterTypes);
        }

        if ($config['use_doctrine_odm']) {
            $loader->load('doctrine_odm.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.doctrine_odm'), $doctrineOdmTemplatesDirs);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.doctrine_odm')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $formTypes = array_merge($this->defaultFormTypes['doctrine_odm'], $config['form_types']['doctrine_odm']);
            $filterTypes = array_merge($this->defaultFilterTypes['doctrine_odm'], $config['filter_types']['doctrine_odm']);
            $container->setParameter('admingenerator.doctrineodm_form_types', $formTypes);
            $container->setParameter('admingenerator.doctrineodm_filter_types', $filterTypes);
        }

        if ($config['use_propel']) {
            $loader->load('propel.xml');
            $this->addTemplatesInitialization($container->getDefinition('admingenerator.generator.propel'), $propelTemplatesDirs);
            if ($config['overwrite_if_exists']) {
                $container
                    ->getDefinition('admingenerator.generator.propel')
                    ->addMethodCall('forceOverwriteIfExists');
            }

            $formTypes = array_merge($this->defaultFormTypes['propel'], $config['form_types']['propel']);
            $filterTypes = array_merge($this->defaultFilterTypes['propel'], $config['filter_types']['propel']);
            $container->setParameter('admingenerator.propel_form_types', $formTypes);
            $container->setParameter('admingenerator.propel_filter_types', $filterTypes);
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
                $container->getParameter('kernel.environment')
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
                $container->getParameter('kernel.environment')
            ));
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
