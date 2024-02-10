<?php

namespace Admingenerator\GeneratorBundle\Filesystem;

use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Finder\Finder;

/**
 * Finds all the *-generator.yml.
 *
 * @author Cedric LOMBARDOT
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class GeneratorsFinder
{
    private ?array $yamls = null;

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * Find all the generator.yml in the bundle and in the kernel Resources folder.
     *
     * @return array An array of yaml files
     */
    public function findAll(): array
    {
        if (null !== $this->yamls) {
            return $this->yamls;
        }

        $yamls = [];

        $finder = new Finder();
        $finder->files()
            ->name('*-generator.yml');
        try {
          $finder->in($this->projectDir . '/src/*/*/Resources/config');
        } catch (DirectoryNotFoundException) {
          try {
            $finder->in($this->projectDir . '/config/admin');
          } catch (DirectoryNotFoundException) {
            return [];
          }
        }

        foreach ($finder as $file) {
            $yamls[$file->getRealPath()] = $file->getRealPath();
        }

        return $this->yamls = $yamls;
    }

    /**
     * Find templates in the given bundle.
     *
     * @param BundleInterface $bundle The bundle where to look for templates
     *
     * @return array of yaml paths
     */
    private function find(BundleInterface $bundle): array
    {
        $yamls = [];

        if (!is_dir($bundle->getPath().'/Resources/config')) {
            return $yamls;
        }

        $finder = new Finder();
        $finder->files()
               ->name('*-generator.yml')
               ->in($bundle->getPath().'/Resources/config');

        foreach ($finder as $file) {
            $yamls[$file->getRealPath()] = $file->getRealPath();
        }

        return $yamls;
    }
}
