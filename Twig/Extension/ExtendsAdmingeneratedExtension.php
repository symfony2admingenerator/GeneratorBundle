<?php

namespace  Admingenerator\GeneratorBundle\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\TokenParser\ExtendsAdmingeneratedTokenParser;
use Twig\Extension\AbstractExtension;

class ExtendsAdmingeneratedExtension extends AbstractExtension
{
    public function getTokenParsers(): array
    {
        return [
            new ExtendsAdmingeneratedTokenParser(),
        ];
    }

    public function getName(): string
    {
        return 'extends_admingenerated';
    }
}
