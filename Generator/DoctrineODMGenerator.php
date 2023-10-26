<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ListBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ListBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\FiltersBuilderType;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ExcelBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\EditBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\EditBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\EditBuilderType;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\NewBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\NewBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\NewBuilderType;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ShowBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ShowBuilderTemplate;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ActionsBuilderAction;
use Admingenerator\GeneratorBundle\Builder\DoctrineODM\ActionsBuilderTemplate;

class DoctrineODMGenerator extends Generator
{
    protected function doBuild(): void
    {
        $this->validateYaml();

        $generator = new AdminGenerator($this->outputDir, $this->getGeneratorYml());
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
        $generator->setTemplateBaseDir('DoctrineODM' . DIRECTORY_SEPARATOR);
        $generator->setBaseController(
            'Admingenerator\GeneratorBundle\Controller\DoctrineODM\BaseController'
        );
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());

        $builders = $generator->getFromYaml('builders', array());

        if (array_key_exists('list', $builders)) {
            $generator->addBuilder(new ListBuilderAction($this->twig));
            $generator->addBuilder(new ListBuilderTemplate($this->twig));
            $generator->addBuilder(new FiltersBuilderType($this->twig));
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
