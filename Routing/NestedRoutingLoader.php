<?php

namespace Admingenerator\GeneratorBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

class NestedRoutingLoader extends RoutingLoader
{
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $this->actions['nested_move'] = [
            'path'         => '/nested-move/{dragged}/{action}/{dropped}',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET'],
            'controller'   => 'list',
        ];

        return parent::load($resource, $type);
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'admingenerator_nested' == $type;
    }
}
