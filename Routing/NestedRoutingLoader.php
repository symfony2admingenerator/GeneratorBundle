<?php

namespace Admingenerator\GeneratorBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

class NestedRoutingLoader extends RoutingLoader
{
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $this->actions['nested_move'] = array(
            'path'         => '/nested-move/{dragged}/{action}/{dropped}',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
            'controller'   => 'list',
        );

        return parent::load($resource, $type);
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'admingenerator_nested' == $type;
    }
}
