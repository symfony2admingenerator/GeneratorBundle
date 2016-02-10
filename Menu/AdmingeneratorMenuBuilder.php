<?php

namespace Admingenerator\GeneratorBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdmingeneratorMenuBuilder
{
    /**
     * @var array
     */
    protected $dividers = array();

    /**
     * @var string
     */
    protected $translation_domain = 'Admingenerator';

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $dashboardRoute;

    public function __construct(FactoryInterface $factory, RequestStack $requestStack, $dashboardRoute)
    {
        $this->factory = $factory;
        $this->requestStack = $requestStack;
        $this->dashboardRoute = $dashboardRoute;
    }

    /**
     * Creates link to uri element and adds it to menu
     * 
     * @param \Knp\Menu\ItemInterface $menu
     * @param string $label Link label
     * @param string $uri
     * @return ItemInterface Link element
     */
    protected function addLinkURI(ItemInterface $menu, $label, $uri)
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
     * 
     * @param \Knp\Menu\ItemInterface $menu
     * @param string $label Link label
     * @param string $route Link route
     * @param array $routeParameters Route parameters
     * @return ItemInterface Link element
     */
    protected function addLinkRoute(ItemInterface $menu, $label, $route, $routeParameters = array())
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
     * 
     * @param ItemInterface $item
     */
    protected function setActive(ItemInterface $item = null)
    {
        if ($item) {
            $this->setActive($item->getParent());
            $item->setAttribute('class', $item->getAttribute('class', '') . ' active');
        }
    }

    /**
     * Creates dropdown menu element and adds it to menu
     * 
     * @param \Knp\Menu\ItemInterface $menu
     * @param string $label Dropdown label
     * @param bool $caret Wheather or not append caret
     * @return ItemInterface Dropdown element
     */
    protected function addDropdown(ItemInterface $menu, $label, $caret = true)
    {
        $item = $this->addLinkURI($menu, $label, '#');
        $item->setChildrenAttributes(array('class' => 'treeview-menu'));
        $item->setAttributes(array('class' => 'treeview'));
        $item->setExtra('caret', $caret);

        return $item;
    }

    /**
     * @param string $uri
     * @return bool
     */
    protected function isCurrentUri($uri)
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getBaseUrl().$request->getPathInfo() === $uri;
    }
}
