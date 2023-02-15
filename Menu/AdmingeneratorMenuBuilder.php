<?php

namespace Admingenerator\GeneratorBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdmingeneratorMenuBuilder
{
    protected array $dividers = [];

    protected string $translation_domain = 'Admingenerator';

    public function __construct(
        protected readonly FactoryInterface $factory,
        protected readonly RequestStack $requestStack,
        protected readonly string $dashboardRoute)
    {
    }

    /**
     * Creates link to uri element and adds it to menu
     */
    protected function addLinkURI(ItemInterface $menu, string $label, string $uri): ItemInterface
    {
        $item = $menu->addChild($label, array('uri' => $uri));
        $item->setExtra('translation_domain', $this->translation_domain);

        if ($this->isCurrentUri($item->getUri())) {
            $item->setAttribute('class', 'active');
        }

        return $item;
    }

    /**
     * Creates link to route element and adds it to menu
     */
    protected function addLinkRoute(ItemInterface $menu, string $label, string $route, array $routeParameters = []): ItemInterface
    {
        $item = $menu->addChild($label, array('route' => $route, 'routeParameters' => $routeParameters, 'routeAbsolute' => UrlGeneratorInterface::ABSOLUTE_PATH));
        $item->setExtra('translation_domain', $this->translation_domain);

        if ($this->isCurrentUri($item->getUri())) {
            $this->setActive($item);
        }

        return $item;
    }

    /**
     * Set active class to current item and all its parents (so it is automatically opened)
     */
    protected function setActive(ItemInterface $item = null): void
    {
        if ($item) {
            $this->setActive($item->getParent());
            $item->setAttribute('class', $item->getAttribute('class', '') . ' active');
        }
    }

    /**
     * Creates dropdown menu element and adds it to menu
     */
    protected function addDropdown(ItemInterface $menu, string $label, bool $caret = true): ItemInterface
    {
        $item = $this->addLinkURI($menu, $label, '#');
        $item->setChildrenAttributes(array('class' => 'treeview-menu'));
        $item->setAttributes(array('class' => 'treeview'));
        $item->setExtra('caret', $caret);

        return $item;
    }

    protected function isCurrentUri(string $uri): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getBaseUrl().$request->getPathInfo() === $uri;
    }
}
