<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Builder\Propel\ListBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\ListBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Propel\NestedListBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\NestedListBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Propel\FiltersBuilderType;
use Admingenerator\GeneratorBundle\Builder\Propel\ExcelBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\EditBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\EditBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Propel\EditBuilderType;
use Admingenerator\GeneratorBundle\Builder\Propel\NewBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\NewBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Propel\NewBuilderType;
use Admingenerator\GeneratorBundle\Builder\Propel\ShowBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\ShowBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\Propel\ActionsBuilderAction;
use Admingenerator\GeneratorBundle\Builder\Propel\ActionsBuilderTemplate;

class PropelGenerator extends Generator
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
        $generator->setBaseController('Admingenerator\GeneratorBundle\Controller\Propel\BaseController');
        $generator->setColumnClass('Admingenerator\GeneratorBundle\Generator\PropelColumn');
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
