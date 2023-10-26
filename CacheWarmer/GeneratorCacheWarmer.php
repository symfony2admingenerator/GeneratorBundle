<?php

namespace Admingenerator\GeneratorBundle\CacheWarmer;

use Admingenerator\GeneratorBundle\CacheBuilder\GeneratorCacheBuilder;
use Admingenerator\GeneratorBundle\Filesystem\GeneratorsFinder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Generate all admingenerated bundle on warmup
 *
 * @author Cedric LOMBARDOT
 */
class GeneratorCacheWarmer implements CacheWarmerInterface
{
    protected GeneratorsFinder $finder;

    public function __construct(protected GeneratorCacheBuilder $generatorCacheBuilder)
    {
    }

    public function warmUp(string $cacheDir): array
    {
        $this->generatorCacheBuilder->buildFull();

        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }
}
