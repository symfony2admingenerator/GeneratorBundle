<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension;

/**
 * This class test the Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension
 *
 * @author Cedric LOMBARDOT
 */
class EchoExtensionTest extends BaseExtensionTest
{
    /**
     * @return \Twig_Extension
     */
    protected function getTestedExtension()
    {
        return new EchoExtension();
    }

    /**
     * @return array
     */
    protected function getTwigVariables()
    {
        $object =  new TestObject();

        return array(
            'obj'  => $object,
            'name' => 'cedric',
            'arr'  => array('obj' => 'val'),
            'arr_num' => array('val'),
            'arr_obj' => array('obj' => $object),
        );
    }

    public function testMapBy()
    {
        $tpls = array(
            'numeric' => '{{ [ arr_num ]|mapBy(0) }}',
            'assoc' => '{{ [ arr ]|mapBy("obj") }}',
            'object' => '{{ [ arr_obj ]|mapBy("foobar") }}',
        );

        $returns = array(
            'numeric' => array(array("val"), 'Correctly mapped array of numeric arrays'),
            'assoc' => array(array("val"), 'Correctly mapped array of assoc arrays'),
            'object' => array(array("foobar"), 'Correctly mapped array of objects'),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testFlatten()
    {
        $tpls = array(
            'numeric' => '{{ ["a", arr_num ]|flatten }}',
            'assoc'   => '{{ {"a", arr_obj }|flatten }}',
        );

        $returns = array(
            'numeric' => array(array("a", "val"), 'Flatten numeric array of arrays'),
            'assoc'   => array(array("a", "val"), 'Flatten associative array of arrays'),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTrans()
    {
        $tpls = array(
            'string' => '{{ echo_trans( "foo" ) }}',
            'variable_key' => '{{ echo_trans( name ) }}',
        );

        $returns = array(
            'string' => array(
                '{% trans from "Admingenerator" %}foo{% endtrans %}',
                'trans return a good trans tag with string elements'
             ),
            'variable_key' => array(
                '{% trans from "Admingenerator" %}cedric{% endtrans %}',
                'trans return a good trans tag with variable as key'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameters()
    {
        $tpls = array(
            'string' => "{{ echo_trans('Display all <b>%foo% %bar%</b> results',{ 'foo': 'foo', 'bar': 'bar' }) }}",
            'variable_key' => '{{ echo_trans( name,{ \'foo\': \'foo\', \'bar\': \'bar\' } ) }}',
        );

        $returns = array(
            'string' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%bar%\': \'bar\',} from "Admingenerator" %}Display all <b>%foo% %bar%</b> results{% endtrans %}',
                'trans return a good trans tag with string elements'
             ),
            'variable_key' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%bar%\': \'bar\',} from "Admingenerator" %}cedric{% endtrans %}',
                'trans return a good trans tag with variable as key'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameterBag()
    {
        $tpls = array(
            'string_bc' => "{{ echo_trans('You\'re editing {{ Book.title }} written by {{ Book.author.name }}!') }}",
            'string_with_full_param_bag' => "{{ echo_trans('You\'re editing %book% written by %author%!|{ %book%: Book.title, %author%: Book.author.name }|') }}",
            'string_with_abbrev_param_bag' => "{{ echo_trans('You\'re editing %Book.title% written by %Book.author.name%!|{ Book.title, Book.author.name }|') }}",
            'string_with_full_param_bag_and_params' => "{{ echo_trans('You\'re editing %book% written by %foo%!|{ %book%: Book.title }|',{ 'foo': 'foo' }) }}",
            'string_with_abbrev_param_bag_and_params' => "{{ echo_trans('You\'re editing %Book.title% written by %foo%!|{ Book.title }|',{ 'foo': 'foo' }) }}",
        );

        $returns = array(
            'string_bc' => array(
                '{% trans with {\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,} from "Admingenerator" %}You\'re editing %Book.title% written by %Book.author.name%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_full_param_bag' => array(
                '{% trans with {\'%book%\': Book.title,\'%author%\': Book.author.name,} from "Admingenerator" %}You\'re editing %book% written by %author%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_abbrev_param_bag' => array(
                '{% trans with {\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,} from "Admingenerator" %}You\'re editing %Book.title% written by %Book.author.name%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_full_param_bag_and_params' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%book%\': Book.title,} from "Admingenerator" %}You\'re editing %book% written by %foo%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_abbrev_param_bag_and_params' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%Book.title%\': Book.title,} from "Admingenerator" %}You\'re editing %Book.title% written by %foo%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoPath()
    {
        $tpls = array(
            'string' => '{{ echo_path( "foo" ) }}',
            'variable' => '{{ echo_path( name ) }}',
            'array' => '{{ echo_path( arr.obj ) }}',
            'string_filtered' => '{{ echo_path( "foo", null, ["foo", "bar"] ) }}',
            'variable_filtered' => '{{ echo_path( name, null, ["foo", "bar"] ) }}',
            'array_filtered' => '{{ echo_path( arr.obj, null, ["foo", "bar"] ) }}',
        );

        $returns = array(
            'string' => array(
                '{{ path("foo") }}',
                'Path return a good Path tag with string elements'
             ),
            'variable' => array(
                '{{ path("cedric") }}',
                'Path return a good Path tag with variable'
             ),
            'array' => array(
                '{{ path("val") }}',
                'Path return a good Path tag with array element'
             ),
            'string_filtered' => array(
                '{{ path("foo")|foo|bar }}',
                'Path return a good Path tag with string elements and filters'
             ),
            'variable_filtered' => array(
                '{{ path("cedric")|foo|bar }}',
                'Path return a good Path tag with variable and filters'
             ),
            'array_filtered' => array(
                '{{ path("val")|foo|bar }}',
                'Path return a good Path tag with array element and filters'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoIfGranted()
    {
        $tpls = array(
            'simple'  => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')" ) }}',
            'complex' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')" ) }}',
            'with_object' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')", \'modelName\' ) }}',
        );

        $returns = array(
            'simple'  => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') %}',
                'If granted work with a simple role'),
            'complex' => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')\') %}',
                'If granted work with a complex role expression'
            ),
            'with_object' => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\', modelName) %}',
                'If granted work with an object'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoRender()
    {
        $tpls = array(
            'controller'  => '{{ echo_render( "MyController" ) }}',
            'with_params' => '{{ echo_render( "MyController", {"hello": name } ) }}',
        );

        $returns = array(
            'controller' => array(
                '{{ render(controller("MyController", {  })) }}',
                'controller return a good controller tag'
            ),
            'with_params' => array(
                '{{ render(controller("MyController", { hello: \'cedric\' })) }}',
                'controller return a good controller tag'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }
}


/**
 * Dummy object for EchoExtensionTest
 *
 * @author Cedric LOMBARDOT
 */
class TestObject
{
    public static $called = array(
        '__toString'  => 0,
        'foo'         => 0,
        'getFooBar'   => 0,
    );

    public function __construct($bar = 'bar')
    {

    }

    public static function reset()
    {
        self::$called = array(
            '__toString'  => 0,
            'foo'         => 0,
            'getFooBar'   => 0,
        );
    }

    public function __toString()
    {
        ++self::$called['__toString'];

        return 'foo';
    }

    public function foo()
    {
        ++self::$called['foo'];

        return 'foo';
    }

    public function getFooBar()
    {
        ++self::$called['getFooBar'];

        return 'foobar';
    }
}
