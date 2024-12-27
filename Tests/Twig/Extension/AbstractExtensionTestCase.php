<?php
namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use LogicException;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;

/**
 * Base class to test extensions. Provide builtin functions to initialize
 * new Twig environment in order to assert a template and its rendered version
 * are coherent.
 */
abstract class AbstractExtensionTestCase extends TestCase
{
    /**
     * Variables used for templates
     */
    protected array $twigVariables = [];

    abstract protected function getTestedExtension(): AbstractExtension;

    abstract protected function getTwigVariables(): array;

    public function setUp(): void
    {
        $this->twigVariables = $this->getTwigVariables();
    }

    protected function runTwigTests(array $templates, array $returns): void
    {
        if ($diff = array_diff(array_keys($templates), array_keys($returns))) {
            throw new LogicException(sprintf(
                'Error: invalid test case. Templates and returns keys mismatch: templates:[%s], returns:[%s] => [%s]',
                implode(', ', array_keys($templates)),
                implode(', ', array_keys($returns)),
                implode(', ', $diff),
            ));
        }
        $twig = $this->getEnvironment($templates);

        foreach ($templates as $name => $tpl) {
            $this->assertEquals(
                $returns[$name][0],
                $twig->render($name, $this->twigVariables),
                $returns[$name][1],
            );
        }
    }

    protected function getEnvironment($templates, $options = []): Environment
    {
        $twig = new Environment(
            new ArrayLoader($templates),
            array_merge(
                [
                    'debug' => true,
                    'cache' => false,
                    'autoescape' => false,
                ],
                $options,
            )
        );
        $twig->addExtension($this->getTestedExtension());

        return $twig;
    }
}
