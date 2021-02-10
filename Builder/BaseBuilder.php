<?php

namespace Admingenerator\GeneratorBundle\Builder;

use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use TwigGenerator\Builder\BaseBuilder as GenericBaseBuilder;
use TwigGenerator\Builder\Generator as GenericBaseGenerator;

abstract class BaseBuilder extends GenericBaseBuilder
{
    /**
     * @var \Admingenerator\GeneratorBundle\Builder\Generator    The generator.
     */
    protected $generator;

    /**
     * @var array
     */
    protected $templatesToGenerate = array();

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $variables;

    /**
     * @var Environment|null
     */
    protected $environment;

    public function getTemplateName()
    {
        if ($this->environment === null) {
            return $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
        }
        return '@AdmingeneratorGenerator/templates/' . $this->getGenerator()->getTemplateBaseDir() . parent::getTemplateName();
    }

    public function __construct(Environment $environment = null)
    {
        parent::__construct();
        $this->variables = new ParameterBag(array());
        $this->twigFilters[] = new TwigFilter('classify', function ($string) {
            return InflectorFactory::create()->build()->classify($string);
        });
        $this->environment = $environment;
    }

    /**
     * Set files to generate
     *
     * @param array $templatesToGenerate (key => template file; value => output file name)
     */
    public function setTemplatesToGenerate(array $templatesToGenerate)
    {
        $this->templatesToGenerate = $templatesToGenerate;
    }

    /**
     * Add a file to generate
     *
     * @param string $template
     * @param string $outputName
     */
    public function addTemplateToGenerate($template, $outputName)
    {
        $this->templatesToGenerate[$template] = $outputName;
    }

    /**
     * Retrieve files to generate.
     *
     * @return array
     */
    public function getTemplatesToGenerate()
    {
        return $this->templatesToGenerate;
    }

    /**
     * Check if builder must generate multiple files
     * based on templatesToGenerate property.
     *
     * @return boolean
     */
    public function isMultiTemplatesBuilder()
    {
        $tmp = $this->getTemplatesToGenerate();

        return !empty($tmp);
    }

    /**
     * (non-PHPdoc)
     * @see \TwigGenerator\Builder\BaseBuilder::writeOnDisk()
     */
    public function writeOnDisk($outputDirectory)
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

    protected function getTwigEnvironment()
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

    /**
     * @return string the YamlKey
     */
    public function getYamlKey()
    {
        return $this->getSimpleClassName();
    }

    public function setVariables(array $variables)
    {
        $variables = new ParameterBag($variables);
        $this->variables = $variables;
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::getVariables()
     */
    public function getVariables()
    {
        return $this->variables->all();
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::hasVariable()
     * @param string $key
     * @return bool
     */
    public function hasVariable($key)
    {
        return $this->variables->has($key);
    }

    /**
     * (non-PHPdoc)
     * @see Builder/Admingenerator\GeneratorBundle\Builder.BuilderInterface::getVariable()
     */
    public function getVariable($key, $default = null, $deep = false)
    {
        return $this->variables->get($key, $default, $deep);
    }

    /**
     * Get model class from model param
     * @return string
     */
    public function getModelClass()
    {
        return $this->getSimpleClassName($this->getVariable('model'));
    }

    /**
     * Set the generator.
     *
     * @param \TwigGenerator\Builder\Generator $generator A generator.
     */
    public function setGenerator(GenericBaseGenerator $generator)
    {
        if (!$generator instanceof Generator) {
            throw new \LogicException(
                '$generator must be an instance of Admingenerator\GeneratorBundle\Builder\Generator, '
               .'other instances are not supported.'
            );
        }

        $this->generator = $generator;
    }

    /**
     * Return the generator.
     *
     * @return \Admingenerator\GeneratorBundle\Builder\Generator    The generator.
     */
    public function getGenerator()
    {
        return $this->generator;
    }
}
