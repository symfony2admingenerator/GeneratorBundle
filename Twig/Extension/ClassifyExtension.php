<?php


namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Doctrine\Inflector\InflectorFactory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ClassifyExtension extends AbstractExtension
{

  public function getFilters(): array
  {
    $options = ['is_safe' => ['html']];
    return array(
        'classify' => new TwigFilter('classify', function ($string) {
            return InflectorFactory::create()->build()->classify($string);
        }, $options),
    );
  }
}