<?php

namespace Admingenerator\GeneratorBundle\CacheBuilder;

use Admingenerator\GeneratorBundle\Filesystem\GeneratorsFinder;
use Admingenerator\GeneratorBundle\Generator\Generator;
use Closure;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class GeneratorCacheBuilder
{
    private readonly GeneratorsFinder $finder;

    public function __construct(private readonly ContainerInterface $container)
    {
        $this->finder = new GeneratorsFinder($this->container->getParameter('kernel.project_dir'));
    }

    public function buildFull(?ProgressBar $progressBar = null): void
    {
        $this->propelInit();

        $this->build(
            function (string $file) {
                $generatorConfiguration = Yaml::parse(file_get_contents($file));

                /** @var Generator $generator */
                $generator = $this->container->get($generatorConfiguration['generator']);
                $generator->setGeneratorYml($file);
                $generator->setBaseGeneratorName($this->getBaseGeneratorName($file));
                $generator->forceOverwriteIfExists();

                $generator->build(true);
            },
            $progressBar,
        );
    }

    public function buildEmpty(string $generationDir): void
    {
        $this->build(function (string $file) use ($generationDir) {
            $emptyGenerator = new EmptyCacheGenerator($generationDir);
            $emptyGenerator->setGeneratorYml($file);
            $emptyGenerator->setBaseGeneratorName($this->getBaseGeneratorName($file));
            $emptyGenerator->build();
        });
    }

    /** @param Closure(string):void $buildClosure Takes the yaml file path to generate the required files */
    protected function build(
        Closure $buildClosure,
        ?ProgressBar $progressBar = null
    ): void
    {
        $yamls = $this->finder->findAll();
        $progressBar?->setMaxSteps(count($yamls));
        foreach ($yamls as $yaml) {
            $progressBar?->advance();
            $buildClosure($yaml);
        }
        $progressBar?->finish();
    }

    protected function getBaseGeneratorName(string $fileName): string
    {
        // windows support too
        if (preg_match('/(?:\/|\\\\)([^\/\\\\]+?)-generator.yml$/', $fileName, $matches)) {
            return ucfirst($matches[1]);
        }

        return '';
    }

    /** Force Propel boot before build */
    protected function propelInit(): void
    {
        if (!$this->container->has('admingenerator.generator.propel')) {
            return;
        }

        if (\Propel::isInit()) {
            return;
        }

        \Propel::setConfiguration($this->container->get('propel.configuration'));
        \Propel::initialize();
    }
}