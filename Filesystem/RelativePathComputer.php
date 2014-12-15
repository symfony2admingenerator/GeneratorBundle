<?php
namespace Admingenerator\GeneratorBundle\Filesystem;

/**
 * Class RelativePathComputer
 * @package Admingenerator\GeneratorBundle\Filesystem
 * @author Stéphane Escandell
 */
class RelativePathComputer
{
    /**
     * @var string
     */
    private $referencePath;

    /**
     * @param $path The path reference
     */
    public function __construct($path)
    {
        $this->referencePath = realpath($path);
    }

    /**
     * Retrieve relative path from reference to $dir
     *
     * @param string $dir
     * @return string
     */
    public function computeToParent($dir)
    {
        if (!$this->isParent($dir)) {
            throw new \LogicException('Targeted dir must be a parent to the reference path.');
        }

        $pathToReference = substr($this->referencePath, strlen($dir));
        $pathToReference = strtr($pathToReference, DIRECTORY_SEPARATOR, '/');
        $subdirs = explode('/', $pathToReference);

        return str_repeat('..' . DIRECTORY_SEPARATOR, count($subdirs)-1);
    }

    /**
     * Check if $dir is a parent to the referenced directory
     *
     * @param $dir
     * @return bool
     */
    public function isParent($dir)
    {
        return 0 === strpos($this->referencePath, realpath($dir));
    }
}
