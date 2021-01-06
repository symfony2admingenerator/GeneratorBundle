<?php

namespace  Admingenerator\GeneratorBundle\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\TokenParser\ExtendsAdmingeneratedTokenParser;
use Twig\Extension\AbstractExtension;

class ExtendsAdmingeneratedExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new ExtendsAdmingeneratedTokenParser(),
        );
    }

    public function getName()
    {
        return 'extends_admingenerated';
    }
}
