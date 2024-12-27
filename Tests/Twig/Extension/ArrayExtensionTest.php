<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\ArrayExtension;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * This class tests the Admingenerator\GeneratorBundle\Twig\Extension\ArrayExtension
 */
class ArrayExtensionTest extends TestCase
{
    private ?ArrayExtension $extension = null;

    public function setUp(): void
    {
        $this->extension = new ArrayExtension();
    }

    public function testMapByWithNumericKey(): void
    {
        $source = array(
            ['val0FromArray1', 'val1FromArray1'],
            ['val0FromArray2', 'val1FromArray2']
        );

        $this->assertEquals(
            ['val0FromArray1', 'val0FromArray2'],
            $this->extension->mapBy($source, 0)
        );

        $this->assertEquals(
            ['val1FromArray1', 'val1FromArray2'],
            $this->extension->mapBy($source, 1)
        );
    }

    public function testMapByWithNamedKey(): void
    {
        $source = [
            ['foo' => 'fooFromArray1', 'bar' => 'barFromArray1'],
            ['foo' => 'fooFromArray2', 'bar' => 'barFromArray2']
        ];

        $this->assertEquals(
            ['barFromArray1', 'barFromArray2'],
            $this->extension->mapBy($source, 'bar')
        );
    }

    public function testMapByOnObject(): void
    {
        $source = [
            new TestObject()
        ];

        $this->assertEquals(
            ['foo'],
            $this->extension->mapBy($source, 'foo')
        );

        $this->assertEquals(
            ['foobar'],
            $this->extension->mapBy($source, 'foobar')
        );
    }

    public function testMapByReturnsExceptionOnNonArrayOrObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->extension->mapBy(['foo'], 0);
    }

    public function testMapByReturnsExceptionOnNonExistingKeyForArray(): void
    {
        $this->expectException(LogicException::class);
        $this->extension->mapBy([['foo']], 5);
    }

    public function testMapByReturnsExceptionOnNonExistingPropertyOrMethodOnObject(): void
    {
        $this->expectException(LogicException::class);
        $this->extension->mapBy([new TestObject()], 'dontExists');
    }

    public function testFlattenWithFlatArrays(): void
    {
        $source = [
            ['foo' => 'fooFromArray1', 'bar' => 'barFromArray1'],
            ['foo' => 'fooFromArray2', 'bar' => 'barFromArray2']
        ];

        $this->assertEquals(
            ['fooFromArray1', 'barFromArray1', 'fooFromArray2', 'barFromArray2'],
            $this->extension->flatten($source)
        );
    }

    public function testFlattenWithNestedArrays(): void
    {
        $source = [
            ['foo' => 'fooFromArray1', 'bar' => ['barFromArray1']],
            ['foo' => 'fooFromArray2', 'bar' => 'barFromArray2']
        ];

        $this->assertEquals(
            ['fooFromArray1', 'barFromArray1', 'fooFromArray2', 'barFromArray2'],
            $this->extension->flatten($source)
        );
    }
}
