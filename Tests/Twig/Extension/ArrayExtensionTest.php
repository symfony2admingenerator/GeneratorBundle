<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\ArrayExtension;

/**
 * This class test the Admingenerator\GeneratorBundle\Twig\Extension\ArrayExtension
 *
 * @author Stéphane Escandell
 */
class ArrayExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayExtension
     */
    private $extension;

    public function setUp()
    {
        $this->extension = new ArrayExtension();
    }


    public function testMapByWithNumericKey()
    {
        $source = array(
            array('val0FromArray1', 'val1FromArray1'),
            array('val0FromArray2', 'val1FromArray2')
        );

        $this->assertEquals(
            array('val0FromArray1', 'val0FromArray2'),
            $this->extension->mapBy($source, 0)
        );

        $this->assertEquals(
            array('val1FromArray1', 'val1FromArray2'),
            $this->extension->mapBy($source, 1)
        );
    }

    public function testMapByWithNamedKey()
    {
        $source = array(
            array('foo' => 'fooFromArray1', 'bar' => 'barFromArray1'),
            array('foo' => 'fooFromArray2', 'bar' => 'barFromArray2')
        );

        $this->assertEquals(
            array('barFromArray1', 'barFromArray2'),
            $this->extension->mapBy($source, 'bar')
        );
    }

    public function testMapByOnObject()
    {
        $source = array(
            new TestObject()
        );

        $this->assertEquals(
            array('foo'),
            $this->extension->mapBy($source, 'foo')
        );

        $this->assertEquals(
            array('foobar'),
            $this->extension->mapBy($source, 'foobar')
        );
    }

    /**
     * @expectedException     \InvalidArgumentException
     */
    public function testMapByReturnsExceptionOnNonArrayOrObject()
    {
        $this->extension->mapBy(array('foo'), 0);
    }

    /**
     * @expectedException     \LogicException
     */
    public function testMapByReturnsExceptionOnNonExistingKeyForArray()
    {
        $this->extension->mapBy(array(array('foo')), 5);
    }

    /**
     * @expectedException     \LogicException
     */
    public function testMapByReturnsExceptionOnNonExistingPropertyOrMethodOnObject()
    {
        $this->extension->mapBy(array(new TestObject()), 'dontExists');
    }


    public function testFlattenWithFlatArrays()
    {
        $source = array(
            array('foo' => 'fooFromArray1', 'bar' => 'barFromArray1'),
            array('foo' => 'fooFromArray2', 'bar' => 'barFromArray2')
        );

        $this->assertEquals(
            array('fooFromArray1', 'barFromArray1', 'fooFromArray2', 'barFromArray2'),
            $this->extension->flatten($source)
        );
    }

    public function testFlattenWithNestedArrays()
    {
        $source = array(
            array('foo' => 'fooFromArray1', 'bar' => array('barFromArray1')),
            array('foo' => 'fooFromArray2', 'bar' => 'barFromArray2')
        );

        $this->assertEquals(
            array('fooFromArray1', 'barFromArray1', 'fooFromArray2', 'barFromArray2'),
            $this->extension->flatten($source)
        );
    }
}
