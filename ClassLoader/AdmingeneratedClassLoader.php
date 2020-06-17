<?php

namespace Admingenerator\GeneratorBundle\ClassLoader;

use Admingenerator\GeneratorBundle\Builder\Admin\EmptyBuilderAction;
use Admingenerator\GeneratorBundle\Builder\EmptyGenerator;

/**
 * This class autoload admingenarated & if they not exists try to generate
 */
class AdmingeneratedClassLoader
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var bool
     */
    public static $initialized = false;

    /**
     * Initialize Admingenerator Class loader
     *
     * @param string $cacheDir
     */
    public static function initAdmingeneratorClassLoader(string $cacheDir)
    {
        $admingeneratedClassLoader = new self();
        $admingeneratedClassLoader->register($cacheDir);
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param string $cacheDir
     */
    public function register(string $cacheDir)
    {
        if (self::$initialized) {
          return;
        }
        $this->basePath = $cacheDir;
        spl_autoload_register(array($this, 'loadClass'), true);
        self::$initialized = true;
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     */
    public function loadClass($class)
    {
        if (0 === strpos($class, 'Admingenerated')) {
            $filePath = $this->basePath.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

            if (!file_exists($filePath)) {
                $this->generateEmptyController($class);
            }

            if (file_exists($filePath)) {
                require $filePath;
            }
        }
    }

    /**
     * @param string $class
     */
    protected function generateEmptyController($class)
    {
        $generator = new EmptyGenerator($this->basePath);

        $parts = explode('\\',$class);
        $controllerName = $parts[count($parts) - 1];
        unset($parts[count($parts) - 1]);

        $namespace = implode('\\', $parts);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $builder = new EmptyBuilderAction();
        $generator->addBuilder($builder);
        $builder->setOutputName($fileName.'.php');

        $builder->setVariables(array(
            'controllerName' => $controllerName,
            'namespace'      => $namespace,
            'require_pk'     => 'ListController' != $controllerName // We don't care about ActionsController and filters
        ));

        $generator->writeOnDisk($this->basePath);
    }

}
