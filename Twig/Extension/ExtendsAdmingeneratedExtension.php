<?php

namespace  Admingenerator\GeneratorBundle\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\TokenParser\ExtendsAdmingeneratedTokenParser;

class ExtendsAdmingeneratedExtension extends \Twig_Extension
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
