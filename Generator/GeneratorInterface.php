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
     * @return void
     */
    public function build();

    /**
     * Force generator to overwrite files if exist.
     * @return void
     */
    public function forceOverwriteIfExists();

    /**
     * Inject the field guesser.
     *
     * @param $fieldGuesser
     */
    public function setFieldGuesser($fieldGuesser);

}
