<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Exception\CantGenerateException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelInterface;
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
     * @var KernelInterface
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
    public function build($forceGeneration = false)
    {
        if (!$forceGeneration && $this->cacheProvider->fetch($this->getCacheKey())) {
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
     * @param KernelInterface $kernel
     * @return void
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param AdminGenerator $generator
     */
    protected function generateEmbedTypes(AdminGenerator $generator, $typeBuilderNamespace)
    {
        foreach ($generator->getFromYaml('params.embed_types', array()) as $yamlPath) {
            $this->buildEmbedTypes($yamlPath, $generator, $typeBuilderNamespace);
        }
    }

    /**
     * @param $yamlPath
     * @param AdminGenerator $generator
     * @throws CantGenerateException
     */
    protected function buildEmbedTypes($yamlPath, AdminGenerator $generator, $typeBuilderNamespace)
    {
        if (preg_match(
            '/(?<namespace_prefix>(?>.+\:)?.+)\:(?<bundle_name>.+Bundle)\:(?<generator_path>.*?)$/',
            $yamlPath,
            $match_string)) {
            $namespace_prefix = $match_string['namespace_prefix'];
            $bundle_name      = $match_string['bundle_name'];
            $generator_path   = $match_string['generator_path'];
        } else {
            $namespace_prefix = $generator->getFromYaml('params.namespace_prefix');
            $bundle_name      = $generator->getFromYaml('params.bundle_name');
            $generator_path   = $yamlPath;
        }

        $yamlFile = $this->kernel->locateResource('@'.$namespace_prefix.$bundle_name.'/Resources/config/'.$generator_path);

        if (!file_exists($yamlFile)) {
            throw new CantGenerateException(
                "Can't generate embed type for $yamlPath: file $yamlFile not found."
            );
        }

        $embedGenerator = new AdminGenerator($this->cache_dir, $yamlFile);
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
        $embedGenerator->setColumnClass($generator->getColumnClass());

        $fqcnEditBuilderType = $typeBuilderNamespace.'\\EditBuilderType';
        $fqcnNewBuilderType = $typeBuilderNamespace.'\\NewBuilderType';
        $embedGenerator->addBuilder(new $fqcnEditBuilderType());
        $embedGenerator->addBuilder(new $fqcnNewBuilderType());

        $embedGenerator->writeOnDisk(
            $this->getCachePath(
                $embedGenerator->getFromYaml('params.namespace_prefix'),
                $embedGenerator->getFromYaml('params.bundle_name')
            )
        );
    }
}
