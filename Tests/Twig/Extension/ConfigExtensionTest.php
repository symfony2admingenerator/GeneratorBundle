<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\ConfigExtension;

/**
 * This class test the Admingenerator\GeneratorBundle\Twig\Extension\ConfigExtension
 *
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ConfigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigExtension
     */
    private $extension;
    
    /**
     * @var array
     */
    private $exampleConfig = array(
        'array1' => array('val0' => 'val0FromArray1', 'val1' => 'val1FromArray1'),
        'array2' => array('val0FromArray2', 'val1FromArray2'),
    );

    public function setUp()
    {
        $this->extension = new ConfigExtension($this->exampleConfig);
    }

    public function testGetAdmingeneratorConfig()
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

    /**
     * @expectedException     \InvalidArgumentException
     */
    public function testGetAdmingeneratorConfigReturnsExceptionOnUnknownKey()
    {
        $this->extension->getAdmingeneratorConfig('unknown.key');
    }
}
