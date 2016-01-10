<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ListBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ListBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Doctrine\NestedListBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\NestedListBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Doctrine\FiltersBuilderType;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ExcelBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\EditBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\EditBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Doctrine\EditBuilderType;
use Admingenerator\GeneratorBundle\Builder\Doctrine\NewBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\NewBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Doctrine\NewBuilderType;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ShowBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ShowBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ActionsBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Doctrine\ActionsBuilderTemplate;

class DoctrineGenerator extends Generator
{
    /**
     * (non-PHPdoc)
     * @see Generator/Admingenerator\GeneratorBundle\Generator.Generator::doBuild()
     */
    protected function doBuild()
    {
        $this->validateYaml();

        $generator = new AdminGenerator($this->cache_dir, $this->getGeneratorYml());
        $generator->setBundleConfig($this->bundleConfig);
        $generator->setRouter($this->router);
        $generator->setBaseAdminTemplate(
            $generator->getFromYaml(
                'base_admin_template',
                $generator->getFromBundleConfig('base_admin_template')
            )
        );
        $generator->setFieldGuesser($this->getFieldGuesser());
        $generator->setMustOverwriteIfExists($this->needToOverwrite($generator));
        $generator->setTwigExtensions($this->twig->getExtensions());
        $generator->setTwigFilters($this->twig->getFilters());
        $generator->setTemplateDirs($this->templatesDirectories);
        $generator->setBaseController(
            'Admingenerator\GeneratorBundle\Controller\Doctrine\BaseController'
        );
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());

        $builders = $generator->getFromYaml('builders', array());

        if (array_key_exists('list', $builders)) {
            $generator->addBuilder(new ListBuilderAction());
            $generator->addBuilder(new ListBuilderTemplate());
            $generator->addBuilder(new FiltersBuilderType());
        }

        if (array_key_exists('nested_list', $builders)) {
            $generator->addBuilder(new NestedListBuilderAction());
            $generator->addBuilder(new NestedListBuilderTemplate());
        }

        if (array_key_exists('edit', $builders)) {
            $generator->addBuilder(new EditBuilderAction());
            $generator->addBuilder(new EditBuilderTemplate());
            $generator->addBuilder(new EditBuilderType());
        }

        if (array_key_exists('new', $builders)) {
            $generator->addBuilder(new NewBuilderAction());
            $generator->addBuilder(new NewBuilderTemplate());
            $generator->addBuilder(new NewBuilderType());
        }

        if (array_key_exists('show', $builders)) {
            $generator->addBuilder(new ShowBuilderAction());
            $generator->addBuilder(new ShowBuilderTemplate());
        }

        if (array_key_exists('excel', $builders)) {
            $generator->addBuilder(new ExcelBuilderAction());
        }

        if (array_key_exists('actions', $builders)) {
            $generator->addBuilder(new ActionsBuilderAction());
            $generator->addBuilder(new ActionsBuilderTemplate());
        }

        $generator->writeOnDisk(
            $this->getCachePath(
                $generator->getFromYaml('params.namespace_prefix'),
                $generator->getFromYaml('params.bundle_name')
            )
        );
    }
}
