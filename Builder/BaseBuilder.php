<?php

namespace Admingenerator\GeneratorBundle\Builder;

use Admingenerator\GeneratorBundle\Twig\Extension\ClassifyExtension;
use LogicException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use TwigGenerator\Builder\BaseBuilder as GenericBaseBuilder;
use TwigGenerator\Builder\Generator as GenericBaseGenerator;

abstract class BaseBuilder extends GenericBaseBuilder
{
    protected GenericBaseGenerator $generator;

    protected array $templatesToGenerate = [];

    protected ?Environment $environment;

    public function getTemplateName(): string
    {
        if ($this->environment === null) {
            return $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
        }
        return '@AdmingeneratorGenerator/templates/' . $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
    }

    public function __construct(Environment $environment = null)
    {
        parent::__construct();
        $this->twigExtensions[] = ClassifyExtension::class;
        $this->environment = $environment;
    }

    /**
     * @param array $templatesToGenerate (key => template file; value => output file name)
     */
    public function setTemplatesToGenerate(array $templatesToGenerate): void
    {
        $this->templatesToGenerate = $templatesToGenerate;
    }

    public function addTemplateToGenerate(string $template, string $outputName): void
    {
        $this->templatesToGenerate[$template] = $outputName;
    }

    public function getTemplatesToGenerate(): array
    {
        return $this->templatesToGenerate;
    }

    /**
     * Check if builder must generate multiple files
     * based on templatesToGenerate property.
     */
    public function isMultiTemplatesBuilder(): bool
    {
        $tmp = $this->getTemplatesToGenerate();

        return !empty($tmp);
    }

    public function writeOnDisk($outputDirectory): void
    {
        if ($this->isMultiTemplatesBuilder()) {
            foreach ($this->getTemplatesToGenerate() as $templateName => $outputName) {
                $this->setOutputName($outputName);
                $this->setTemplateName($templateName);
                parent::writeOnDisk($outputDirectory);
            }
        } else {
            parent::writeOnDisk($outputDirectory);
        }
    }

    protected function getTwigEnvironment(): Environment
    {
        if($this->environment !== null) {
            return $this->environment;
        }
        $loader = new FilesystemLoader($this->getTemplateDirs());
        $twig = new Environment($loader, array(
            'autoescape' => false,
            'strict_variables' => true,
            'debug' => true,
            'cache' => $this->getGenerator()->getTempDir(),
        ));

        $this->loadTwigExtensions($twig);
        $this->loadTwigFilters($twig);

        return $twig;
    }

    public function getYamlKey(): string
    {
        return $this->getSimpleClassName();
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function hasVariable($key): bool
    {
        return array_key_exists($key, $this->variables);
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::getVariable()
     */
    public function getVariable(string $key, $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }

    public function getModelClass(): string
    {
        return $this->getSimpleClassName($this->getVariable('model'));
    }

    public function setGenerator(GenericBaseGenerator $generator): void
    {
        if (!$generator instanceof Generator) {
            throw new LogicException(
                '$generator must be an instance of Admingenerator\GeneratorBundle\Builder\Generator, '
               .'other instances are not supported.'
            );
        }

        $this->generator = $generator;
    }

    public function getGenerator(): Generator
    {
        $generator = $this->generator;
        assert($generator instanceof Generator);
        return $generator;
    }
}
