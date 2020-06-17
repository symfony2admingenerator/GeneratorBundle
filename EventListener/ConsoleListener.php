<?php


namespace Admingenerator\GeneratorBundle\EventListener;


use Admingenerator\GeneratorBundle\ClassLoader\AdmingeneratedClassLoader;

class ConsoleListener
{

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * ConsoleListener constructor.
     *
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Initialize the classloader for console commands
     */
    public function onConsoleCommand()
    {
        AdmingeneratedClassLoader::initAdmingeneratorClassLoader($this->cacheDir);
    }
}