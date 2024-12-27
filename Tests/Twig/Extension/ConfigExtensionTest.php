<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\ConfigExtension;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * This class tests the Admingenerator\GeneratorBundle\Twig\Extension\ConfigExtension
 */
class ConfigExtensionTest extends TestCase
{
    private ?ConfigExtension $extension = null;

    private array $exampleConfig = [
        'array1' => ['val0' => 'val0FromArray1', 'val1' => 'val1FromArray1'],
        'array2' => ['val0FromArray2', 'val1FromArray2'],
    ];

    public function setUp(): void
    {
        $this->extension = new ConfigExtension($this->exampleConfig);
    }

    public function testGetAdmingeneratorConfig(): void
    {
        $this->assertEquals(
            $this->exampleConfig['array1']['val0'],
            $this->extension->getAdmingeneratorConfig('array1.val0')
        );
        
        $this->assertEquals(
            $this->exampleConfig['array2'],
            $this->extension->getAdmingeneratorConfig('array2')
        );
    }

    public function testGetAdmingeneratorConfigReturnsExceptionOnUnknownKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->extension->getAdmingeneratorConfig('unknown.key');
    }
}
