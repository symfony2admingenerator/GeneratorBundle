<?php

namespace Admingenerator\GeneratorBundle\Validator;

use Symfony\Component\Yaml\Yaml;
use Admingenerator\GeneratorBundle\Generator\Generator;

class BaseValidator
{
    protected function getFromYaml(Generator $generator, string $yaml_path, mixed $default = null): mixed
    {
        $search_in = Yaml::parse(file_get_contents($generator->getGeneratorYml()));

        $yaml_path = explode('.',$yaml_path);
        foreach ($yaml_path as $key) {
            if (!isset($search_in[$key])) {
                return $default;
            }
            $search_in = $search_in[$key];
        }

        return $search_in;
    }
}
