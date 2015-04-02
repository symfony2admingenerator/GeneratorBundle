<?php

namespace Admingenerator\GeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * @author clombardot
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $rootName;

    /**
     * @param string $rootName
     */
    public function __construct($rootName)
    {
        $this->rootName = $rootName;
    }

    /**
     * Generates the configuration tree builder.
     *
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root($this->rootName);

        $rootNode
            ->children()
                ->booleanNode('use_doctrine_orm')->defaultFalse()->end()
                ->booleanNode('use_doctrine_odm')->defaultFalse()->end()
                ->booleanNode('use_propel')->defaultFalse()->end()
                ->booleanNode('guess_required')->defaultTrue()->end()
                ->booleanNode('default_required')->defaultTrue()->end()
                ->booleanNode('overwrite_if_exists')->defaultFalse()->end()
                ->scalarNode('base_admin_template')
                    ->defaultValue("AdmingeneratorGeneratorBundle::base.html.twig")
                ->end()
                ->scalarNode('dashboard_route')->defaultNull()->end()
                ->scalarNode('login_route')->defaultNull()->end()
                ->scalarNode('logout_route')->defaultNull()->end()
                ->scalarNode('exit_route')->defaultNull()->end()
                ->scalarNode('generator_cache')->defaultNull()->end()
                ->scalarNode('default_action_after_save')->defaultValue('edit')->end()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('use_form_resources')->defaultTrue()->end()
                        ->booleanNode('use_localized_date')->defaultFalse()->end()
                        ->scalarNode('date_format')->defaultValue('Y-m-d')->end()
                        ->scalarNode('datetime_format')->defaultValue('Y-m-d H:i:s')->end()
                        ->scalarNode('localized_date_format')->defaultValue('medium')->end()
                        ->scalarNode('localized_datetime_format')->defaultValue('medium')->end()
                        ->arrayNode('number_format')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('decimal')->defaultValue(0)->end()
                                ->scalarNode('decimal_point')->defaultValue('.')->end()
                                ->scalarNode('thousand_separator')->defaultValue(',')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates_dirs')
                    ->prototype('scalar')->end()
                ->end()
                //defaults are loaded in the AdminGeneratorGeneratorExtension
                ->arrayNode('form_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('propel')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                //defaults are loaded in the AdminGeneratorGeneratorExtension
                ->arrayNode('filter_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('propel')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->getStylesheetNode())
                ->append($this->getJavascriptsNode())
            ->end();

        return $treeBuilder;
    }

    private function getStylesheetNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('stylesheets');

        $node
            ->prototype('array')
            ->fixXmlConfig('stylesheets')
                ->children()
                    ->scalarNode('path')->end()
                    ->scalarNode('media')->defaultValue('all')->end()
                ->end()
            ->end();

        return $node;
    }

    private function getJavascriptsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('javascripts');

        $node
            ->prototype('array')
            ->fixXmlConfig('javascripts')
                ->children()
                    ->scalarNode('path')->end()
                    ->scalarNode('route')->end()
                    ->arrayNode('routeparams')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
