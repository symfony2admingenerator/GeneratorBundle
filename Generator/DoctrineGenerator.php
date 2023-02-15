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
    protected function doBuild(): void
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
        $generator->setTemplateBaseDir('Doctrine' . DIRECTORY_SEPARATOR);
        $generator->setBaseController(
            'Admingenerator\GeneratorBundle\Controller\Doctrine\BaseController'
        );
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());

        $builders = $generator->getFromYaml('builders', array());

        if (array_key_exists('list', $builders)) {
            $generator->addBuilder(new ListBuilderAction($this->twig));
            $generator->addBuilder(new ListBuilderTemplate($this->twig));
            $generator->addBuilder(new FiltersBuilderType($this->twig));
        }

        if (array_key_exists('nested_list', $builders)) {
            $generator->addBuilder(new NestedListBuilderAction($this->twig));
            $generator->addBuilder(new NestedListBuilderTemplate($this->twig));
        }

        if (array_key_exists('edit', $builders)) {
            $generator->addBuilder(new EditBuilderAction($this->twig));
            $generator->addBuilder(new EditBuilderTemplate($this->twig));
            $generator->addBuilder(new EditBuilderType($this->twig));
        }

        if (array_key_exists('new', $builders)) {
            $generator->addBuilder(new NewBuilderAction($this->twig));
            $generator->addBuilder(new NewBuilderTemplate($this->twig));
            $generator->addBuilder(new NewBuilderType($this->twig));
        }

        if (array_key_exists('show', $builders)) {
            $generator->addBuilder(new ShowBuilderAction($this->twig));
            $generator->addBuilder(new ShowBuilderTemplate($this->twig));
        }

        if (array_key_exists('excel', $builders)) {
            $generator->addBuilder(new ExcelBuilderAction($this->twig));
        }

        if (array_key_exists('actions', $builders)) {
            $generator->addBuilder(new ActionsBuilderAction($this->twig));
            $generator->addBuilder(new ActionsBuilderTemplate($this->twig));
        }

        $generator->writeOnDisk(
            $this->getCachePath(
                $generator->getFromYaml('params.namespace_prefix'),
                $generator->getFromYaml('params.bundle_name')
            )
        );
    }
}
