<?php

namespace Admingenerator\GeneratorBundle\Filesystem;

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
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var array
     */
    private $yamls;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Find all the generator.yml in the bundle and in the kernel Resources folder.
     *
     * @return array An array of yaml files
     */
    public function findAll()
    {
        if (null !== $this->yamls) {
            return $this->yamls;
        }

        $yamls = array();

        foreach ($this->kernel->getBundles() as $name => $bundle) {
            foreach ($this->find($bundle) as $yaml) {
                $yamls[$yaml] = $yaml;
            }
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
    private function find(BundleInterface $bundle)
    {
        $yamls =  array();

        if (!file_exists($bundle->getPath().'/Resources/config')) {
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
