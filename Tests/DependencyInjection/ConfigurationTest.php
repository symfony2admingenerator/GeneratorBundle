<?php

namespace Admingenerator\GeneratorBundle\Tests\DependencyInjection;

use Admingenerator\GeneratorBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * Test that default configuration is correctly initialized
     */
    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration('admingen_generator'), array());

        $this->assertEquals($this->getBundleDefaultConfig(), $config);
    }

    /**
     * Get waiting default values from configuration. If $key is not null
     * and is in first level keys, returns value of this specific key only.
     */
    private function getBundleDefaultConfig(string $key = null): mixed
    {
        static $defaultConfiguration = [
            'generate_base_in_project_dir'  => false,
            'use_doctrine_orm'              => false,
            'use_doctrine_odm'              => false,
            'use_propel'                    => false,
            'use_jms_security'              => false,
            'guess_required'                => true,
            'default_required'              => true,
            'overwrite_if_exists'           => false,
            'base_admin_template'           => '@AdmingeneratorGenerator/base.html.twig',
            'dashboard_route'               => null,
            'login_route'                   => null,
            'logout_route'                  => null,
            'exit_route'                    => null,
            'generator_cache'               => null,
            'knp_menu_alias'                => null,
            'use_doctrine_orm_batch_remove' => false,
            'use_doctrine_odm_batch_remove' => false,
            'use_propel_batch_remove'       => false,
            'twig'                  => [
                'use_form_resources'        => true,
                'use_localized_date'        => false,
                'date_format'               => 'Y-m-d',
                'datetime_format'           => 'Y-m-d H:i:s',
                'localized_date_format'     => 'medium',
                'localized_datetime_format' => 'medium',
                'number_format'             => [
                    'decimal'               => 0,
                    'decimal_point'         => '.',
                    'thousand_separator'    => ','
                ]
            ],
            'templates_dirs'        => [],
            'form_types'            => [
                'doctrine_orm'  => [
                    'datetime'      => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'vardatetime'   => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'datetimetz'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'date'          => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'time'          => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'decimal'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'float'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'integer'       => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'bigint'        => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'smallint'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'string'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'text'          => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'entity'        => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    'collection'    => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'array'         => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'boolean'       => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                ],
                'doctrine_odm'  => [
                    'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'timestamp'   => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'int'         => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'integer'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'int_id'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'id'          => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'custom_id'   => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'document'    => 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType',
                    'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'hash'        => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                ],
                'propel'        => [
                    'TIMESTAMP'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'BU_TIMESTAMP' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'DATE'         => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'BU_DATE'      => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'TIME'         => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'FLOAT'        => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'REAL'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'DOUBLE'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'DECIMAL'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'TINYINT'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'SMALLINT'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'INTEGER'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'BIGINT'       => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'NUMERIC'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'CHAR'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'VARCHAR'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'LONGVARCHAR'  => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'BLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'CLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'CLOB_EMU'     => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
                    'model'        => 'Symfony\Bridge\Propel1\Form\Type\ModelType',
                    'collection'   => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'PHP_ARRAY'    => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'ENUM'         => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    'BOOLEAN'      => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                    'BOOLEAN_EMU'  => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
                ],
            ],
            'filter_types'          => [
                'doctrine_orm'  => [
                    'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'integer'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'entity'      => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'array'       => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                ],
                'doctrine_odm'  => [
                    'datetime'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'timestamp'   => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'vardatetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'datetimetz'  => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'date'        => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'time'        => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'decimal'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'float'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'int'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'integer'     => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'int_id'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'bigint'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'smallint'    => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'id'          => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'custom_id'   => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'string'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'text'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'document'    => 'Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType',
                    'collection'  => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'hash'        => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'boolean'     => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                ],
                'propel'        => [
                    'TIMESTAMP'    => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'BU_TIMESTAMP' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
                    'DATE'         => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'BU_DATE'      => 'Symfony\Component\Form\Extension\Core\Type\DateType',
                    'TIME'         => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
                    'FLOAT'        => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'REAL'         => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'DOUBLE'       => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'DECIMAL'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'TINYINT'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'SMALLINT'     => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'INTEGER'      => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'BIGINT'       => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    'NUMERIC'      => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
                    'CHAR'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'VARCHAR'      => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'LONGVARCHAR'  => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'BLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'CLOB'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'CLOB_EMU'     => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'model'        => 'Symfony\Bridge\Propel1\Form\Type\ModelType',
                    'collection'   => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
                    'PHP_ARRAY'    => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'ENUM'         => 'Symfony\Component\Form\Extension\Core\Type\TextType',
                    'BOOLEAN'      => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    'BOOLEAN_EMU'  => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                ],
            ],
            'stylesheets'               => [],
            'javascripts'               => [],
            'default_action_after_save' => 'edit',
            'throw_exceptions'          => false,
            'generate_base_in_project_dir_directory' => 'admin',
        ];

        if (!is_null($key) && array_key_exists($key, $defaultConfiguration)) {
            return $defaultConfiguration[$key];
        }

        return $defaultConfiguration;
    }
}
