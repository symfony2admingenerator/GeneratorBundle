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

    private $defaultFormTypes = array(
        'doctrine_orm' => array(
            // datetime types
            'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'integer'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            // text types
            'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            // association types
            'entity'      => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // array types
            'array'       => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // boolean types
            'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
        ),
        'doctrine_odm' => array(
            // datetime types
            'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'timestamp'   => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'int'         => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'integer'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'int_id'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            // text types
            'id'          => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'custom_id'   => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            // association types
            'document'    => 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType',
            'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // hash types
            'hash'        => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // boolean types
            'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
        ),
        'propel'       => array(
            // datetime types
            'TIMESTAMP'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'BU_TIMESTAMP' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            // date types
            'DATE'         => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            'BU_DATE'      => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'TIME'         => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'FLOAT'        => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'REAL'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'DOUBLE'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'DECIMAL'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'TINYINT'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'SMALLINT'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'INTEGER'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'BIGINT'       => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'NUMERIC'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            // text types
            'CHAR'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'VARCHAR'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'LONGVARCHAR'  => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            'BLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            'CLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            'CLOB_EMU'     => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            // association types
            'model'        => 'Symfony\Bridge\Propel1\Form\Type\ModelType',
            'collection'   => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // array types
            'PHP_ARRAY'    => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // choice types
            'ENUM'         => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            // boolean types
            'BOOLEAN'      => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            'BOOLEAN_EMU'  => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
        ));

    private $defaultFilterTypes = array(
        'doctrine_orm' => array(
            // datetime types
            'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'integer'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // text types
            'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // association types
            'entity'      => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // array types
            'array'       => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // boolean types
            'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        ),
        'doctrine_odm' => array(
            // datetime types
            'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'timestamp'   => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'int'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'integer'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'int_id'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // text types
            'id'          => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'custom_id'   => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // association types
            'document'    => 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType',
            'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // hash types
            'hash'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // boolean types
            'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        ),
        'propel' => array(
            // datetime types
            'TIMESTAMP'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'BU_TIMESTAMP' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            // date types
            'DATE'         => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            'BU_DATE'      => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            // time types
            'TIME'         => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            // number types
            'FLOAT'        => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'REAL'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'DOUBLE'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            'DECIMAL'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // integer types
            'TINYINT'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'SMALLINT'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'INTEGER'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'BIGINT'       => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            'NUMERIC'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            // text types
            'CHAR'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'VARCHAR'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // textarea types
            'LONGVARCHAR'  => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'BLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'CLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'CLOB_EMU'     => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // association types
            'model'        => 'Symfony\Bridge\Propel1\Form\Type\ModelType',
            'collection'   => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            // array types
            'PHP_ARRAY'    => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // choice types
            'ENUM'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            // boolean types
            'BOOLEAN'      => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            'BOOLEAN_EMU'  => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        ));

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
                ->booleanNode('use_propel2')->defaultFalse()->end()
                ->booleanNode('use_jms_security')->defaultFalse()->end()
                ->booleanNode('guess_required')->defaultTrue()->end()
                ->booleanNode('default_required')->defaultTrue()->end()
                ->booleanNode('overwrite_if_exists')->defaultFalse()->end()
                ->booleanNode('throw_exceptions')->defaultFalse()->end()
                ->scalarNode('base_admin_template')
                    ->defaultValue("AdmingeneratorGeneratorBundle::base.html.twig")
                ->end()
                ->scalarNode('dashboard_route')->defaultNull()->end()
                ->scalarNode('login_route')->defaultNull()->end()
                ->scalarNode('logout_route')->defaultNull()->end()
                ->scalarNode('exit_route')->defaultNull()->end()
                ->scalarNode('generator_cache')->defaultNull()->end()
                ->scalarNode('default_action_after_save')->defaultValue('edit')->end()
                ->scalarNode('knp_menu_alias')->defaultNull()->end()
                ->booleanNode('use_doctrine_orm_batch_remove')->defaultFalse()->end()
                ->booleanNode('use_doctrine_odm_batch_remove')->defaultFalse()->end()
                ->booleanNode('use_propel_batch_remove')->defaultFalse()->end()
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
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFormTypes['doctrine_orm'])
                            ->validate()
                            ->ifNotInArray(array_keys($this->defaultFormTypes['doctrine_orm']))
                                ->then(function($v) {
                                    return array_merge($this->defaultFormTypes['doctrine_orm'], $v);
                                })
                            ->end()
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFormTypes['doctrine_odm'])
                            ->validate()
                            ->ifNotInArray(array_values($this->defaultFormTypes['doctrine_odm']))
                                ->then(function ($v){
                                    return array_merge($this->defaultFormTypes['doctrine_odm'], $v);
                                })
                            ->end()
                        ->end()
                        ->arrayNode('propel')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFormTypes['propel'])
                            ->validate()
                            ->ifNotInArray(array_values($this->defaultFormTypes['propel']))
                                ->then(function ($v){
                                    return array_merge($this->defaultFormTypes['propel'], $v);
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('filter_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('doctrine_orm')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFilterTypes['doctrine_orm'])
                            ->validate()
                            ->ifNotInArray(array_values($this->defaultFilterTypes['doctrine_orm']))
                                ->then(function ($v){
                                    return array_merge($this->defaultFilterTypes['doctrine_orm'], $v);
                                })
                            ->end()
                        ->end()
                        ->arrayNode('doctrine_odm')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFilterTypes['doctrine_odm'])
                            ->validate()
                            ->ifNotInArray(array_keys($this->defaultFilterTypes['doctrine_odm']))
                                ->then(function ($v){
                                    return array_merge($this->defaultFilterTypes['doctrine_odm'], $v);
                                })
                            ->end()
                        ->end()
                        ->arrayNode('propel')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                            ->defaultValue($this->defaultFilterTypes['propel'])
                            ->validate()
                            ->ifNotInArray(array_values($this->defaultFilterTypes['propel']))
                                ->then(function ($v){
                                    return array_merge($this->defaultFilterTypes['propel'], $v);
                                })
                            ->end()
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
