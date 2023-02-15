<?php

namespace Admingenerator\GeneratorBundle\CacheWarmer;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Admingenerator\GeneratorBundle\Filesystem\GeneratorsFinder;

/**
 * Generate all admingenerated bundle on warmup
 *
 * @author Cedric LOMBARDOT
 */
class GeneratorCacheWarmer implements CacheWarmerInterface
{
    protected GeneratorsFinder $finder;

    public function __construct(protected ContainerInterface $container)
    {
        $this->finder = new GeneratorsFinder($container->getParameter('kernel.project_dir'));
    }

    public function warmUp(string $cacheDir): array
    {
        if ($this->container->has('admingenerator.generator.propel')) {
            $this->propelInit();
        }

        foreach ($this->finder->findAll() as $yaml) {
            try {
                $this->buildFromYaml($yaml);
            } catch (\Exception $e) {
                while ($e->getPrevious()) {
                    $e = $e->getPrevious();
                }
                echo ">> Skip warmup ".
                    $e->getMessage().
                    ".\nIn file ".
                    $e->getFile().
                    " on line ".
                    $e->getLine().
                    ".\nBacktrace:\n".
                    $e->getTraceAsString();
            }
        }
        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }

    protected function buildFromYaml($file): void
    {
        $generatorConfiguration = Yaml::parse(file_get_contents($file));
        $generator = $this->container->get($generatorConfiguration['generator']);
        $generator->setGeneratorYml($file);

        // windows support too
        if (preg_match('/(?:\/|\\\\)([^\/\\\\]+?)-generator.yml$/', $file, $matches)) {
            $generator->setBaseGeneratorName(ucfirst($matches[1]));
        } else {
            $generator->setBaseGeneratorName('');
        }

        $generator->build(true);
    }

    /**
     * Force Propel boot before cache warmup
     */
    protected function propelInit(): void
    {
        if (!\Propel::isInit()) {
            \Propel::setConfiguration($this->container->get('propel.configuration'));
            \Propel::initialize();
        }
    }
}
