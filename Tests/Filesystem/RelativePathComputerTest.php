<?php
namespace Admingenerator\GeneratorBundle\Tests\Filesystem;

use Admingenerator\GeneratorBundle\Filesystem\RelativePathComputer;
use LogicException;
use PHPUnit\Framework\TestCase;

class RelativePathComputerTest extends TestCase
{
    public function testComputingToParent(): void
    {
        $referencePath = __FILE__;

        $computer = new RelativePathComputer($referencePath);
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 1), $computer->computeToParent(dirname($referencePath)));
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 3), $computer->computeToParent(dirname($referencePath, 3)));

        $referencePath = dirname($referencePath);
        $computer = new RelativePathComputer($referencePath);
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 1), $computer->computeToParent(dirname($referencePath)));
        $this->assertEquals(str_repeat('..' . DIRECTORY_SEPARATOR, 3), $computer->computeToParent(dirname($referencePath, 3)));
    }

    public function testExceptionThrowIfNotParent(): void
    {
        $computer = new RelativePathComputer(__FILE__);
        $this->expectException(LogicException::class);

        $computer->computeToParent(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Fake');
    }
}
