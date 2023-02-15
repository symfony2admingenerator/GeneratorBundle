<?php
namespace Admingenerator\GeneratorBundle\Filesystem;

use LogicException;

/**
 * Class RelativePathComputer
 * @package Admingenerator\GeneratorBundle\Filesystem
 * @author Stéphane Escandell
 */
class RelativePathComputer
{
    private string $referencePath;

    /**
     * @param string $path The path reference
     */
    public function __construct(string $path)
    {
        $this->referencePath = realpath($path);
    }

    /**
     * Retrieve relative path from reference to $dir
     */
    public function computeToParent(string $dir): string
    {
        if (!$this->isParent($dir)) {
            throw new LogicException('Targeted dir must be a parent to the reference path.');
        }

        $pathToReference = substr($this->referencePath, strlen($dir));
        $pathToReference = strtr($pathToReference, DIRECTORY_SEPARATOR, '/');
        $subdirs = explode('/', $pathToReference);

        return str_repeat('..' . DIRECTORY_SEPARATOR, count($subdirs)-1);
    }

    /**
     * Check if $dir is a parent to the referenced directory
     */
    public function isParent(string $dir): bool
    {
        return str_starts_with($this->referencePath, realpath($dir));
    }
}
