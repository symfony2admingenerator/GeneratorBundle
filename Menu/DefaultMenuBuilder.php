<?php

namespace Admingenerator\GeneratorBundle\Menu;

use Admingenerator\GeneratorBundle\Menu\AdmingeneratorMenuBuilder;
use Knp\Menu\FactoryInterface;

class DefaultMenuBuilder extends AdmingeneratorMenuBuilder
{
    public function sidebarMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'sidebar-menu'));

        if ($dashboardRoute = $this->container->getParameter('admingenerator.dashboard_route')) {
            $this
                ->addLinkRoute($menu, 'admingenerator.dashboard', $dashboardRoute)
                ->setExtra('icon', 'fa fa-dashboard');
        }
        
        $overwrite = $this->addDropdown($menu, 'Replace this menu');

        $this->addLinkURI(
            $overwrite,
            'Create new menu builder',
            'https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle'
            .'/blob/master/Resources/doc/cookbook/menu.md'
        )->setExtra('icon', 'fa fa-wrench');

        $this->addLinkURI(
            $overwrite,
            'Customize menu block',
            'https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle'.
            '/blob/master/Resources/views/base_admin_navbar.html.twig'
        )->setExtra('icon', 'fa  fa-code-fork');
        
        return $menu;
    }
}
