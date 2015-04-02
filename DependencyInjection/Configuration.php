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
                ->arrayNode('form_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'datetime'      => 'datetime'
                                'vardatetime'   => 'datetime'
                                'datetimetz'    => 'datetime'
                                'date'          => 'date'
                                // time types
                                'time'          => 'time'
                                // number types
                                'decimal'       => 'number'
                                'float'         => 'number'
                                // integer types
                                'integer'       => 'integer'
                                'bigint'        => 'integer'
                                'smallint'      => 'integer'
                                // text types
                                'string'        => 'text'
                                // textarea types
                                'text'          => 'textarea'
                                // association types
                                'entity'        => 'entity'
                                'collection'    => 'collection'
                                // array types
                                'array'         => 'collection'
                                // boolean types
                                'boolean'       => 'checkbox'
                            ))
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'datetime'      => 'datetime'
                                'timestamp'     => 'datetime'
                                'vardatetime'   => 'datetime'
                                'datetimetz'    => 'datetime'
                                'date'          => 'date'
                                // time types
                                'time'          => 'time'
                                // number types
                                'decimal'       => 'number'
                                'float'         => 'number'
                                // integer types
                                'int'           => 'integer'
                                'integer'       => 'integer'
                                'int_id'        => 'integer'
                                'bigint'        => 'integer'
                                'smallint'      => 'integer'
                                // text types
                                'id'            => 'text'
                                'custom_id'     => 'text'
                                'string'        => 'text'
                                // textarea types
                                'text'          => 'textarea'
                                // association types
                                'document'      => 'document'
                                'collection'    => 'collection'
                                // hash types
                                'hash'          => 'collection'
                                // boolean types
                                'boolean'       => 'checkbox'
                            ))
                        ->end()
                        ->arrayNode('propel')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'TIMESTAMP'     => 'datetime'
                                'BU_TIMESTAMP'  => 'datetime'
                                // date types
                                'DATE'          => 'date'
                                'BU_DATE'       => 'date'
                                // time types
                                'TIME'          => 'time'
                                // number types
                                'FLOAT'         => 'number'
                                'REAL'          => 'number'
                                'DOUBLE'        => 'number'
                                'DECIMAL'       => 'number'
                                // integer types
                                'TINYINT'       => 'integer'
                                'SMALLINT'      => 'integer'
                                'INTEGER'       => 'integer'
                                'BIGINT'        => 'integer'
                                'NUMERIC'       => 'integer'
                                // text types
                                'CHAR'          => 'text'
                                'VARCHAR'       => 'text'
                                // textarea types
                                'LONGVARCHAR'   => 'textarea'
                                'BLOB'          => 'textarea'
                                'CLOB'          => 'textarea'
                                'CLOB_EMU'      => 'textarea'
                                // association types
                                'model'         => 'model'
                                'collection'    => 'collection'
                                // array types
                                'PHP_ARRAY'     => 'collection'
                                // choice types
                                'ENUM'          => 'choice'
                                // boolean types
                                'BOOLEAN'       => 'checkbox'
                                'BOOLEAN_EMU'   => 'checkbox'
                            ))
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('filter_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'datetime'      => 'datetime'
                                'vardatetime'   => 'datetime'
                                'datetimetz'    => 'datetime'
                                'date'          => 'date'
                                // time types
                                'time'          => 'time'
                                // number types
                                'decimal'       => 'number'
                                'float'         => 'number'
                                // integer types
                                'integer'       => 'number'
                                'bigint'        => 'number'
                                'smallint'      => 'number'
                                // text types
                                'string'        => 'text'
                                // textarea types
                                'text'          => 'text'
                                // association types
                                'entity'        => 'model'
                                'collection'    => 'collection'
                                // array types
                                'array'         => 'text'
                                // boolean types
                                'boolean'       => 'choice'
                            ))
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'datetime'      => 'datetime'
                                'timestamp'     => 'datetime'
                                'vardatetime'   => 'datetime'
                                'datetimetz'    => 'datetime'
                                'date'          => 'date'
                                // time types
                                'time'          => 'time'
                                // number types
                                'decimal'       => 'number'
                                'float'         => 'number'
                                // integer types
                                'int'           => 'number'
                                'integer'       => 'number'
                                'int_id'        => 'number'
                                'bigint'        => 'number'
                                'smallint'      => 'number'
                                // text types
                                'id'            => 'text'
                                'custom_id'     => 'text'
                                'string'        => 'text'
                                // textarea types
                                'text'          => 'text'
                                // association types
                                'document'      => 'model'
                                'collection'    => 'collection'
                                // hash types
                                'hash'          => 'text'
                                // boolean types
                                'boolean'       => 'choice'
                            ))
                        ->end()
                        ->arrayNode('propel')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')->end()
                            ->defaultValue(array(
                                // datetime types
                                'TIMESTAMP'     => 'datetime'
                                'BU_TIMESTAMP'  => 'datetime'
                                // date types
                                'DATE'          => 'date'
                                'BU_DATE'       => 'date'
                                // time types
                                'TIME'          => 'time'
                                // number types
                                'FLOAT'         => 'number'
                                'REAL'          => 'number'
                                'DOUBLE'        => 'number'
                                'DECIMAL'       => 'number'
                                // integer types
                                'TINYINT'       => 'number'
                                'SMALLINT'      => 'number'
                                'INTEGER'       => 'number'
                                'BIGINT'        => 'number'
                                'NUMERIC'       => 'number'
                                // text types
                                'CHAR'          => 'text'
                                'VARCHAR'       => 'text'
                                // textarea types
                                'LONGVARCHAR'   => 'text'
                                'BLOB'          => 'text'
                                'CLOB'          => 'text'
                                'CLOB_EMU'      => 'text'
                                // association types
                                'model'         => 'model'
                                'collection'    => 'collection'
                                // array types
                                'PHP_ARRAY'     => 'text'
                                // choice types
                                'ENUM'          => 'text'
                                // boolean types
                                'BOOLEAN'       => 'choice'
                                'BOOLEAN_EMU'   => 'choice'
                            ))
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
