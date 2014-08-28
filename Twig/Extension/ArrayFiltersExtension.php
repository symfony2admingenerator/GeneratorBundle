<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ArrayFiltersExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'intersect' => new \Twig_Filter_Method($this, 'intersect'),
        );
    }

    public function intersect()
    {
        return call_user_func_array('array_intersect', func_get_args());
    }

    public function flatten(array $input)
    {
        return array_values(new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($input)
        ));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admingenerator_array';
    }
}
