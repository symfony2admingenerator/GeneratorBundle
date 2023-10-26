<?php

namespace Admingenerator\GeneratorBundle\CacheBuilder;

use Admingenerator\GeneratorBundle\Builder\Admin\EmptyBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Generator\Generator;

class EmptyCacheGenerator extends Generator
{
    protected function doBuild(): void
    {
        $generator = new class(sys_get_temp_dir(), $this->getGeneratorYml()) extends AdminGenerator {
        };

        $generator->setMustOverwriteIfExists(false);
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());

        $namespace = $generator->getFromYaml('params.namespace_prefix');
        $bundleName = $generator->getFromYaml('params.bundle_name');

        $builders = $generator->getFromYaml('builders', []);
        $controllerNames = [];
        if (array_key_exists('list', $builders)) {
            $controllerNames[] = 'List';
        }

        if (array_key_exists('nested_list', $builders)) {
            $controllerNames[] = 'NestedList';
        }

        if (array_key_exists('edit', $builders)) {
            $controllerNames[] = 'Edit';
        }

        if (array_key_exists('new', $builders)) {
            $controllerNames[] = 'New';
        }

        if (array_key_exists('show', $builders)) {
            $controllerNames[] = 'Show';
        }

        if (array_key_exists('excel', $builders)) {
            $controllerNames[] = 'Excel';
        }

        if (array_key_exists('actions', $builders)) {
            $controllerNames[] = 'Actions';
        }

        // Add the empty action builders
        foreach ($controllerNames as $controllerName) {
            $controllerName = $controllerName . 'Controller';
            $fullNamespace = 'Admingenerated\\' . $namespace . $bundleName . '\\' . $generator->getGeneratedControllerFolder();

            $generator->addBuilder($builder = new EmptyBuilderAction());
            $builder->setOutputName($generator->getGeneratedControllerFolder() . DIRECTORY_SEPARATOR . $controllerName . '.php');
            $builder->setVariables([
                'controllerName' => $controllerName,
                'namespace' => $fullNamespace,
                'require_pk' => 'ListController' != $controllerName,
                'generateBaseInProjectDir' => true,
            ]);

            $generator->writeOnDisk($this->getCachePath($namespace, $bundleName));
        }
    }
}