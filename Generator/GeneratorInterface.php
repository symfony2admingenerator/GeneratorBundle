<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Guesser\FieldGuesser;

interface GeneratorInterface
{

    /**
     * Give the cache path to save the files
     */
    public function getCachePath(string $namespace, string $bundleName): string;

    /**
     * Run builders & create files in cache
     */
    public function build(): void;

    /**
     * Force generator to overwrite files if they exist.
     */
    public function forceOverwriteIfExists(): void;

    /**
     * Inject the field guesser.
     */
    public function setFieldGuesser(FieldGuesser $fieldGuesser): void;

}
