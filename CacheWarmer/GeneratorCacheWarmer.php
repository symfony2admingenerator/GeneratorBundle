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
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var GeneratorsFinder
     */
    protected $finder;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The dependency injection container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->finder = new GeneratorsFinder($container->get('kernel'));
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
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
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always false
     */
    public function isOptional()
    {
        return false;
    }

    protected function buildFromYaml($file)
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
    protected function propelInit()
    {
        if (class_exists('Propel')) { // Propel 1
            if (!\Propel::isInit()) {
                \Propel::setConfiguration($this->container->get('propel.configuration'));
                \Propel::initialize();
            }

            return;
        }
    }
}
