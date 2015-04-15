<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Admingenerator\GeneratorBundle\Exception\CantGenerateException;
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
            'Admingenerator\GeneratorBundle\Controller\DoctrineODM\BaseController'
        );
        $generator->setBaseGeneratorName($this->getBaseGeneratorName());

        $embed_types = $generator->getFromYaml('params.embed_types', array());

        foreach ($embed_types as $yaml_path) {
            $this->prebuildEmbedType($yaml_path, $generator);
        }

        $builders = $generator->getFromYaml('builders', array());

        if (array_key_exists('list', $builders)) {
            $generator->addBuilder(new ListBuilderAction());
            $generator->addBuilder(new ListBuilderTemplate());
            $generator->addBuilder(new FiltersBuilderType());
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

    public function prebuildEmbedType($yaml_path, AdminGenerator $generator)
    {
        $pattern_string = '/(?<namespace_prefix>(?>.+\:)?.+)\:(?<bundle_name>.+Bundle)\:(?<generator_path>.*?)$/';

        if (preg_match($pattern_string, $yaml_path, $match_string)) {
            $namespace_prefix = $match_string['namespace_prefix'];
            $bundle_name      = $match_string['bundle_name'];
            $generator_path   = $match_string['generator_path'];
        } else {
            $namespace_prefix = $generator->getFromYaml('params.namespace_prefix');
            $bundle_name      = $generator->getFromYaml('params.bundle_name');
            $generator_path   = $yaml_path;
        }

        $yaml_file = $this->kernel->locateResource('@'.$namespace_prefix.$bundle_name.'/Resources/config/'.$generator_path);

        if (!file_exists($yaml_file)) {
            throw new CantGenerateException(
                "Can't generate embed type for $yaml_file, file not found."
            );
        }

        $embedGenerator = new AdminGenerator($this->cache_dir, $yaml_file);
        $embedGenerator->setBundleConfig($this->bundleConfig);
        $embedGenerator->setRouter($this->router);
        $embedGenerator->setBaseAdminTemplate(
            $embedGenerator->getFromYaml(
                'base_admin_template',
                $embedGenerator->getFromBundleConfig('base_admin_template')
            )
        );
        $embedGenerator->setFieldGuesser($this->getFieldGuesser());
        $embedGenerator->setMustOverwriteIfExists($this->needToOverwrite($embedGenerator));
        $embedGenerator->setTwigExtensions($this->twig->getExtensions());
        $embedGenerator->setTwigFilters($this->twig->getFilters());
        $embedGenerator->setTemplateDirs($this->templatesDirectories);

        $embedGenerator->addBuilder(new EditBuilderType());
        $embedGenerator->addBuilder(new NewBuilderType());

        $embedGenerator->writeOnDisk(
            $this->getCachePath(
                $embedGenerator->getFromYaml('params.namespace_prefix'),
                $generator->getFromYaml('params.bundle_name')
            )
        );
    }
}
