<?php

namespace Admingenerator\GeneratorBundle\Tests\ClassLoader;

use Admingenerator\GeneratorBundle\Tests\TestCase;
use Admingenerator\GeneratorBundle\ClassLoader\AdmingeneratedClassLoader;
use PHPUnit\Framework\Attributes\DataProvider;

class AdmingeneratedClassLoaderTest extends TestCase
{
    #[DataProvider('getLoadClassTests')]
    public function testLoadClass($className, $testClassName, $message): void
    {
        $loader = new AdmingeneratedClassLoader();
        $loader->register(realpath(sys_get_temp_dir()));
        $loader->loadClass($testClassName);
        $this->assertTrue(class_exists($className), $message);
    }

    public static function getLoadClassTests(): array
    {
        return [
            [
                '\\Admingenerated\\AdmingeneratorDemoBundle\\BaseController\\ListController',
                'Admingenerated\\AdmingeneratorDemoBundle\\BaseController\\ListController',
                '->loadClass() loads admingenerated class'
            ],
        ];
    }
}
