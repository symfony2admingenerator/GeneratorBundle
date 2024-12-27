<?php

namespace Admingenerator\GeneratorBundle\ClassLoader;

use Admingenerator\GeneratorBundle\Builder\Admin\EmptyBuilderAction;
use Admingenerator\GeneratorBundle\Builder\EmptyGenerator;

/**
 * This class autoload admingenerated & if they not exist try to generate
 */
class AdmingeneratedClassLoader
{
    protected string $basePath;

    public static bool $initialized = false;

    public static function initAdmingeneratorClassLoader(string $cacheDir): void
    {
        $admingeneratedClassLoader = new self();
        $admingeneratedClassLoader->register($cacheDir);
    }

    public function register(string $cacheDir): void
    {
        if (self::$initialized) {
          return;
        }
        $this->basePath = $cacheDir;
        spl_autoload_register([$this, 'loadClass'], true);
        self::$initialized = true;
    }

    public function loadClass(string $class): void
    {
        if (str_starts_with($class, 'Admingenerated')) {
            $filePath = $this->basePath.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

            if (!file_exists($filePath)) {
                $this->generateEmptyController($class);
            }

            if (file_exists($filePath)) {
                require $filePath;
            }
        }
    }

    protected function generateEmptyController(string $class): void
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
