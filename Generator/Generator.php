<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Admingenerator\GeneratorBundle\Validator\ValidatorInterface;
use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Doctrine\Common\Cache as DoctrineCache;
use Twig_Environment as TwigEnvironment;

abstract class Generator implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $root_dir;

    /**
     * @var string
     */
    protected $cache_dir;

    /**
     * @var string
     */
    protected $generator_yaml;

    /**
     * @var array $bundleConfig Generator bundle config.
     */
    protected $bundleConfig;

    /**
     * @var object $fieldGuesser The fieldguesser.
     */
    protected $fieldGuesser;

    /**
     * @var string
     */
    protected $baseGeneratorName;

    /**
     * @var array
     */
    protected $validators = array();

    /**
     * @var DoctrineCache\CacheProvider
     */
    protected $cacheProvider;

    /**
     * @var string
     */
    protected $cacheSuffix = 'default';

    /**
     * @var array
     */
    protected $templatesDirectories = array();

    /**
     * @var bool
     */
    protected $overwriteIfExists = false;

    /**
     * @var RouterInterface
     */
    protected $router;
    
    /**
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     * @param $root_dir
     * @param $cache_dir
     */
    public function __construct($root_dir, $cache_dir)
    {
        $this->root_dir = $root_dir;
        $this->cache_dir = $cache_dir;
        $this->cacheProvider = new DoctrineCache\ArrayCache();
    }

    /**
     * @param DoctrineCache\CacheProvider $cacheProvider
     * @param string $cacheSuffix
     * @return void
     */
    public function setCacheProvider(DoctrineCache\CacheProvider $cacheProvider, $cacheSuffix = 'default')
    {
        $this->cacheProvider = $cacheProvider;
        $this->cacheSuffix = $cacheSuffix;
    }

    /**
     * Force overwrite files if exists mode.
     * @return void
     */
    public function forceOverwriteIfExists()
    {
        $this->overwriteIfExists = true;
    }

    /**
     * @param $directory
     * @return void
     */
    public function addTemplatesDirectory($directory)
    {
        $this->templatesDirectories[] = $directory;
    }

    /**
     * @param $yaml_file
     * @return void
     */
    public function setGeneratorYml($yaml_file)
    {
        $this->generator_yaml = $yaml_file;
    }

    /**
     * @return string
     */
    public function getGeneratorYml()
    {
        return $this->generator_yaml;
    }

    /**
     * @param $baseGeneratorName
     * @return void
     */
    public function setBaseGeneratorName($baseGeneratorName)
    {
        $this->baseGeneratorName = $baseGeneratorName;
    }

    /**
     * @return string
     */
    protected function getBaseGeneratorName()
    {
        return $this->baseGeneratorName;
    }

    /**
     * (non-PHPdoc)
     * @see Generator/Admingenerator\GeneratorBundle\Generator.GeneratorInterface::getCachePath()
     */
    public function getCachePath($namespace, $bundle_name)
    {
        return $this->cache_dir.'/Admingenerated/'.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).$bundle_name;
    }

    /**
     * (non-PHPdoc)
     * @see Generator/Admingenerator\GeneratorBundle\Generator.GeneratorInterface::build()
     */
    public function build()
    {
        if ($this->cacheProvider->fetch($this->getCacheKey())) {
            return;
        }

        $this->doBuild();
        $this->cacheProvider->save($this->getCacheKey(), true);
    }

    /**
     * Process build
     */
    abstract protected function doBuild();

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return sprintf('admingen_isbuilt_%s_%s', $this->getBaseGeneratorName(), $this->cacheSuffix);
    }

    /**
     * @param object $fieldGuesser The fieldguesser.
     * @return void
     */
    public function setFieldGuesser($fieldGuesser)
    {
        $this->fieldGuesser = $fieldGuesser;
    }

    /**
     * @return object The fieldguesser.
     */
    public function getFieldGuesser()
    {
        return $this->fieldGuesser;
    }

    /**
     * Check if we have to build file
     */
    public function needToOverwrite(AdminGenerator $generator)
    {
        if ($this->overwriteIfExists) {
            return true;
        }

        $cacheDir = $this->getCachePath($generator->getFromYaml('params.namespace_prefix'), $generator->getFromYaml('params.bundle_name'));

        if (!is_dir($cacheDir)) {
            return true;
        }

        $fileInfo = new \SplFileInfo($this->getGeneratorYml());

        $finder = new Finder();
        $files = $finder->files()
            ->date('< '.date('Y-m-d H:i:s',$fileInfo->getMTime()))
            ->in($cacheDir)
            ->count();

        if ($files > 0) {
            return true;
        }

        $finder = new Finder();
        foreach ($finder->files()->in($cacheDir) as $file) {
            if (false !== strpos(file_get_contents($file), 'AdmingeneratorEmptyBuilderClass')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function addValidator(ValidatorInterface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * @return void
     */
    public function validateYaml()
    {
        foreach ($this->validators as $validator) {
            $validator->validate($this);
        }
    }

    /**
     * @param array $bundleConfig
     * @return void
     */
    public function setBundleConfig(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return void
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }
    
    /**
     * @param TwigEnvironment $twig
     * @return void
     */
    public function setTwig(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }
    
    /**
     * @param HttpKernelInterface $kernel
     * @return void
     */
    public function setKernel(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}
