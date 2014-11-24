<?php

namespace Admingenerator\GeneratorBundle\Generator;

interface GeneratorInterface
{

    /**
     * Give the cache path to save the files
     *
     * @param string $namespace   The namespace of the bundle
     * @param string $bundle_name the bundle name
     *
     * @return string
     */
    public function getCachePath($namespace, $bundle_name);

    /**
     * Run builders & create files in cache
     */
    public function build();

    /**
     * Force generator to overwrite files if exist.
     */
    public function forceOverwriteIfExists();

    /**
     * Inject the field guesser.
     *
     * @param $fieldGuesser
     */
    public function setFieldGuesser($fieldGuesser);

    /**
     * Add the $directory as a root directory for templates files.
     * If $prepend is true, prepend the directory to the list so
     * templates in that directory are prioritize.
     *
     * @param $directory
     * @param bool $prepend
     * @return mixed
     */
    public function addTemplatesDirectory($directory, $prepend = false);

}
