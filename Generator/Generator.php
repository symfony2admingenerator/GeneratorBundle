<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Guesser\FieldGuesser;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Admingenerator\GeneratorBundle\Validator\ValidatorInterface;
use Admingenerator\GeneratorBundle\Builder\Generator as AdminGenerator;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

abstract class Generator implements GeneratorInterface
{
    protected ?string $generatorYml = null;

    protected array $bundleConfig = [];

    protected FieldGuesser $fieldGuesser;

    protected ?string $baseGeneratorName = null;

    protected array $validators = [];

    protected string $cacheSuffix = 'default';

    protected array $templatesDirectories = [];

    protected bool $overwriteIfExists = false;

    protected RouterInterface $router;

    protected Environment $twig;

    protected KernelInterface $kernel;

    public function __construct(
        protected readonly string $outputDir,
        protected CacheInterface $cacheProvider = new ArrayAdapter())
    {
    }

    public function setCacheProvider(CacheInterface $cacheProvider, string $cacheSuffix = 'default'): void
    {
        $this->cacheProvider = $cacheProvider;
        $this->cacheSuffix = $cacheSuffix;
    }

    public function forceOverwriteIfExists(): void
    {
        $this->overwriteIfExists = true;
    }

    public function addTemplatesDirectory(string $directory): void
    {
        $this->templatesDirectories[] = $directory;
    }

    public function setGeneratorYml(string $yamlFile): void
    {
        $this->generatorYml = $yamlFile;
    }

    public function getGeneratorYml(): ?string
    {
        return $this->generatorYml;
    }

    public function setBaseGeneratorName(string $baseGeneratorName): void
    {
        $this->baseGeneratorName = $baseGeneratorName;
    }

    protected function getBaseGeneratorName(): ?string
    {
        return $this->baseGeneratorName;
    }

    public function getCachePath(string $namespace, string $bundleName): string
    {
        return $this->outputDir.'/Admingenerated/'.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).$bundleName;
    }

    public function build($forceGeneration = false): void
    {
        if (!$forceGeneration && $this->cacheProvider->get($this->getCacheKey(), fn () => false)) {
            return;
        }

        $this->doBuild();
        $this->cacheProvider->get($this->getCacheKey(), fn () => true);
    }

    abstract protected function doBuild(): void;

    protected function getCacheKey(): string
    {
        return str_replace(str_split('@{}\/:'), '_', sprintf('admingen_isbuilt_%s_%s', $this->getBaseGeneratorName(), $this->cacheSuffix));
    }

    public function setFieldGuesser(FieldGuesser $fieldGuesser): void
    {
        $this->fieldGuesser = $fieldGuesser;
    }

    public function getFieldGuesser(): FieldGuesser
    {
        return $this->fieldGuesser;
    }

    public function needToOverwrite(AdminGenerator $generator): bool
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
            if (str_contains(file_get_contents($file), 'AdmingeneratorEmptyBuilderClass')) {
                return true;
            }
        }

        return false;
    }

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    public function validateYaml(): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($this);
        }
    }

    public function setBundleConfig(array $bundleConfig): void
    {
        $this->bundleConfig = $bundleConfig;
    }

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }
}
