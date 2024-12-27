<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension;
use Twig\Extension\AbstractExtension;

/**
 * This class tests the Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension
 */
class EchoExtensionTest extends AbstractExtensionTestCase
{
    protected bool $useJms = false;

    protected function getTestedExtension(): EchoExtension
    {
        return new EchoExtension($this->useJms);
    }

    protected function getTwigVariables(): array
    {
        $object =  new TestObject();

        return [
            'obj'  => $object,
            'name' => 'cedric',
            'arr'  => ['obj' => 'val'],
        ];
    }

    public function testGetEchoTrans(): void
    {
        $tpls = [
            'string' => '{{ echo_trans( "foo" ) }}',
            'variable_key' => '{{ echo_trans( name ) }}',
            'quote_included' => '{{ echo_trans( "My awesome \"title\"") }}'
        ];

        $returns = [
            'string' => [
                '{{ "foo"|trans({}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'variable_key' => [
                '{{ "cedric"|trans({}, "Admingenerator")|raw }}',
                'trans return a good trans tag with variable as key'
            ],
            'quote_included' => [
                '{{ "My awesome \"title\""|trans({}, "Admingenerator")|raw }}',
                'trans return a good trans tag with variable as key'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithEscape(): void
    {
        $tpls = [
            'string' => '{{ echo_trans( "foo", {}, "Admingenerator", "html_attr" ) }}',
            'variable_key' => '{{ echo_trans( name, {}, "Admingenerator", "html_attr" ) }}',
        ];

        $returns = [
            'string' => [
                '{{ "foo"|trans({}, "Admingenerator")|escape("html_attr") }}',
                'trans return a good trans tag with string elements'
            ],
            'variable_key' => [
                '{{ "cedric"|trans({}, "Admingenerator")|escape("html_attr") }}',
                'trans return a good trans tag with variable as key'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameters(): void
    {
        $tpls = [
            'string' => "{{ echo_trans('Display all <b>%foo% %bar%</b> results', { 'foo': 'foo', 'bar': 'bar' }) }}",
            'variable_key' => '{{ echo_trans(name, { \'foo\': \'foo\', \'bar\': \'bar\' }) }}',
            'quote_in_param_value' => '{{ echo_trans(name, { \'foo\': \'foo\\\'s\', \'bar\': \'bar\' }) }}',
        ];

        $returns = [
            'string' => [
                '{{ "Display all <b>%foo% %bar%</b> results"|trans({\'%foo%\': \'foo\',\'%bar%\': \'bar\',}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'variable_key' => [
                '{{ "cedric"|trans({\'%foo%\': \'foo\',\'%bar%\': \'bar\',}, "Admingenerator")|raw }}',
                'trans return a good trans tag with variable as key'
            ],
            'quote_in_param_value' => [
                '{{ "cedric"|trans({\'%foo%\': \'foo\\\'s\',\'%bar%\': \'bar\',}, "Admingenerator")|raw }}',
                'trans return a good trans tag with variable as key'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameterBag(): void
    {
        $tpls = [
            'string_bc' => "{{ echo_trans('You\'re editing {{ Book.title }} written by {{ Book.author.name }}!') }}",
            'string_with_full_param_bag' => "{{ echo_trans('You\'re editing %book% written by %author%!|{ %book%: Book.title, %author%: Book.author.name }|') }}",
            'string_with_abbrev_param_bag' => "{{ echo_trans('You\'re editing %Book.title% written by %Book.author.name%!|{ Book.title, Book.author.name }|') }}",
            'string_with_full_param_bag_and_params' => "{{ echo_trans('You\'re editing %book% written by %foo%!|{ %book%: Book.title }|',{ 'foo': 'foo' }) }}",
            'string_with_abbrev_param_bag_and_params' => "{{ echo_trans('You\'re editing %Book.title% written by %foo%!|{ Book.title }|',{ 'foo': 'foo' }) }}",
        ];

        $returns = [
            'string_bc' => [
                '{{ "You\'re editing %Book.title% written by %Book.author.name%!"|trans({\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'string_with_full_param_bag' => [
                '{{ "You\'re editing %book% written by %author%!"|trans({\'%book%\': Book.title,\'%author%\': Book.author.name,}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'string_with_abbrev_param_bag' => [
                '{{ "You\'re editing %Book.title% written by %Book.author.name%!"|trans({\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'string_with_full_param_bag_and_params' => [
                '{{ "You\'re editing %book% written by %foo%!"|trans({\'%foo%\': \'foo\',\'%book%\': Book.title,}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
            'string_with_abbrev_param_bag_and_params' => [
                '{{ "You\'re editing %Book.title% written by %foo%!"|trans({\'%foo%\': \'foo\',\'%Book.title%\': Book.title,}, "Admingenerator")|raw }}',
                'trans return a good trans tag with string elements'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoPath(): void
    {
        $tpls = [
            'string' => '{{ echo_path( "foo" ) }}',
            'variable' => '{{ echo_path( name ) }}',
            'array' => '{{ echo_path( arr.obj ) }}',
            'string_filtered' => '{{ echo_path( "foo", null, ["foo", "bar"] ) }}',
            'variable_filtered' => '{{ echo_path( name, null, ["foo", "bar"] ) }}',
            'array_filtered' => '{{ echo_path( arr.obj, null, ["foo", "bar"] ) }}',
        ];

        $returns = [
            'string' => [
                '{{ path("foo") }}',
                'Path return a good Path tag with string elements'
            ],
            'variable' => [
                '{{ path("cedric") }}',
                'Path return a good Path tag with variable'
            ],
            'array' => [
                '{{ path("val") }}',
                'Path return a good Path tag with array element'
            ],
            'string_filtered' => [
                '{{ path("foo")|foo|bar }}',
                'Path return a good Path tag with string elements and filters'
            ],
            'variable_filtered' => [
                '{{ path("cedric")|foo|bar }}',
                'Path return a good Path tag with variable and filters'
            ],
            'array_filtered' => [
                '{{ path("val")|foo|bar }}',
                'Path return a good Path tag with array element and filters'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoIfGrantedWithoutJms(): void
    {
        $this->useJms = false;
        $tpls = [
            'simple'  => '{{ echo_if_granted ( "ROLE_A" ) }}',
            'with_object' => '{{ echo_if_granted ( "ROLE_A", \'modelName\' ) }}',
        ];

        $returns = [
            'simple'  => [
                '{% if is_granted(\'ROLE_A\') %}',
                'If granted work with a simple role'],
            'with_object' => [
                '{% if is_granted(\'ROLE_A\', modelName is defined ? modelName : null) %}',
                'If granted work with an object'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoIfGrantedWithJms(): void
    {
        $this->useJms = true;
        $tpls = [
            'simple'  => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')" ) }}',
            'complex' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')" ) }}',
            'with_object' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')", \'modelName\' ) }}',
        ];

        $returns = [
            'simple'  => [
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') %}',
                'If granted work with a simple role'],
            'complex' => [
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')\') %}',
                'If granted work with a complex role expression'
            ],
            'with_object' => [
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\', modelName is defined ? modelName : null) %}',
                'If granted work with an object'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoRender(): void
    {
        $tpls = [
            'controller'  => '{{ echo_render( "MyController" ) }}',
            'with_params' => '{{ echo_render( "MyController", {"hello": name } ) }}',
        ];

        $returns = [
            'controller' => [
                '{{ render(controller("MyController", {  })) }}',
                'controller return a good controller tag'
            ],
            'with_params' => [
                '{{ render(controller("MyController", { hello: \'cedric\' })) }}',
                'controller return a good controller tag'
            ],
        ];

        $this->runTwigTests($tpls, $returns);
    }

    public function testConvertAsForm()
    {
        $tpls = [
            'no_modifications' => "{{ 'my string'|convert_as_form('unused') }}",
            'no_modifications_on_specific_characters' => "{{ '\"\'&<>;{}()[]\\\/'|convert_as_form('unused') }}",
            'query_builder' => "{{ \"'query_builder' => 'function(\$er) { return \$er->createQueryBuilder(); }',\"|convert_as_form('MyEntityType') }}",
            'query' => "{{ \"'query' => 'function() { return MyModel::GetPeerTable(); }',\"|convert_as_form('ModelType') }}",
            'php_call' => "{{ \"'__php(strtolower(\'TeSt\'))'\"|convert_as_form('unused') }}",
            'php_call_and_string' => "{{ \"'__php(strtolower(\'TeSt\'))','my other string'\"|convert_as_form('unused') }}",
        ];

        $returns = [
            'no_modifications' => ['my string', "convert_as_form doesn't modify string"],
            'no_modifications_on_specific_characters' => ["\"'&<>;{}()[]\\/", "convert_as_form doesn't modify specific characters"],
            'query_builder' => ["'query_builder' => function(\$er) { return \$er->createQueryBuilder(); },", 'convert_as_form properly transforms query_builder'],
            'query' => ["'query' => function() { return MyModel::GetPeerTable(); },", 'convert_as_form properly transforms query'],
            'php_call' => ["strtolower('TeSt')", 'convert_as_form properly transforms __php'],
            'php_call_and_string' => ["strtolower('TeSt'),'my other string'", 'convert_as_form properly transforms __php'],
        ];

        $this->runTwigTests($tpls, $returns);
    }
}
