<?php
namespace Admingenerator\GeneratorBundle\Tests\Filesystem;

use Admingenerator\GeneratorBundle\Filesystem\RelativePathComputer;

/**
 * Class RelativePathComputerTest
 * @package Admingenerator\GeneratorBundle\Tests\Filesystem
 * @author Stéphane Escandell
 */
class RelativePathComputerTest extends \PHPUnit_Framework_TestCase
{
    public function testComputingToParent()
    {
        $referencePath = __FILE__;

        $computer = new RelativePathComputer($referencePath);
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 1), $computer->computeToParent(dirname($referencePath)));
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 3), $computer->computeToParent(dirname(dirname(dirname($referencePath)))));

        $referencePath = dirname($referencePath);
        $computer = new RelativePathComputer($referencePath);
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 1), $computer->computeToParent(dirname($referencePath)));
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 3), $computer->computeToParent(dirname(dirname(dirname($referencePath)))));
    }

    public function testExceptionThrowIfNotParent()
    {
        $computer = new RelativePathComputer(__FILE__);
        $this->setExpectedException('\LogicException');

        $computer->computeToParent(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Fake');
    }
}