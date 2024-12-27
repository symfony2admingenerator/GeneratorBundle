<?php
namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

/**
 * Dummy object for EchoExtensionTest
 */
class TestObject
{
    public static array $called = array(
        '__toString'  => 0,
        'foo'         => 0,
        'getFooBar'   => 0,
    );

    public function __construct($bar = 'bar')
    {

    }

    public static function reset(): void
    {
        self::$called = [
            '__toString'  => 0,
            'foo'         => 0,
            'getFooBar'   => 0,
        ];
    }

    public function __toString()
    {
        ++self::$called['__toString'];

        return 'foo';
    }

    public function foo(): string
    {
        ++self::$called['foo'];

        return 'foo';
    }

    public function getFooBar(): string
    {
        ++self::$called['getFooBar'];

        return 'foobar';
    }
}
