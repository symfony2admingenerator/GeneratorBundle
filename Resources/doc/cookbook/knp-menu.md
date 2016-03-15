# Menu

[go back to Table of contents][back-to-index]

-----

The KnpMenuBundle can be used to automatically generate menu structures for your admin generator.
This doc describes the installation and configuration of the menu bundle.

### 1. Installation

To install the bundle, simply add `"knplabs/knp-menu-bundle": ">1.0,<2.2"` to your `composer.json`
and run a `composer update`. 

Also, enable the bundle in your `app/AppKernel.php`:
```php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Admingenerator\GeneratorBundle\AdmingeneratorGeneratorBundle($this),
        new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
    );
}
```

### 2. Template configuration

The admingenerator ships with a template which can be directly used by the KnpMenuBundle:
`AdmingeneratorGeneratorBundle:KnpMenu:knp_menu_trans.html.twig`. Simply set the `knp_menu.twig.template` to 
the template to use it.

This template enables:

* prepending menu items with icons
* appending caret to dropdown menu items
* translation of menu item labels

If you change the template to a custom one, you will have to copy some lines from 
`AdmingeneratorGeneratorBundle:KnpMenu:knp_menu_trans.html.twig` to have these features.

### 2. Enable the menu bundle

You can enable the default admin generator menu by setting the `knp_menu_alias` parameter to `admingen_sidebar` in the global config. 
The admin generator will then include the `admingen_sidebar` menu, which is configured to render the 
[default menu][default-builder] (see the [sidebar layout template][sidebar-layout]). However, do not forget to 
define the `admingen_sidebar` configuration:

```xml
<service id="admingen.menu.default_builder" class="Admingenerator\GeneratorBundle\Menu\DefaultMenuBuilder">
    <argument type="service" id="knp_menu.factory" />
    <argument type="service" id="request_stack" />
    <tag name="knp_menu.menu_builder" method="sidebarMenu" alias="admingen_sidebar" />
</service>
```

You will also need to add the following line in your bundles Extension file (to configure the dashboard route):
```php
public function load(array $configs, ContainerBuilder $container)
{
    $container->getDefinition('admingen.menu.default_builder')->addArgument(
        $container->getParameter('admingenerator.dashboard_route')
    );
}
```

### 3. Customize the menu

#### Create new menu builder

To overwrite the default menu builder, you need to [create][create-builder] a new menu builder class. 
To make things easier Admingenerator ships a [base][extend-builder] class which you can extend 
(see [default][default-builder] menu builder to an example).

> **Note**: In case of extending the default builder, your own menu builder has to be defined as a 
service (see [Creating Menus as Services][create-service-builder], or check the xml configuration above).

Also, do not forget to update the `knp_menu_alias` with your new menu alias.

### 4. Example

```php
public function navbarMenu(FactoryInterface $factory, array $options)
{
    // create root item
    $menu = $factory->createItem('root');
    // set id for root item, and class for nice twitter bootstrap style
    $menu->setChildrenAttributes(array('id' => 'main_navigation', 'class' => 'nav navbar-nav'));

    // add links $menu
    $this->addLinkURI($menu, 'Item1', 'http://www.google.com');
    $this->addLinkRoute($menu, 'Item2', 'Your_RouteName');

    // add dropdown to $menu
    $dropdown = $this->addDropdown($menu, 'Item 3', true);

    // add header to $dropdown
    $this->addHeader($dropdown, 'Heading');

    // add links to $dropdown
    $this->addLinkRoute($dropdown, 'Subitem 3.1', 'Your_RouteName');
    $this->addLinkURI($dropdown, 'Subitem 3.2', 'http://www.google.com');

    // add more links to $dropdown
    $this->addLinkRoute($dropdown, 'Subitem 3.3', 'Your_RouteName');

    return $menu;
}
```

[back-to-index]: ../documentation.md
[sidebar-layout]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/views/Sidebar/layout.html.twig
[default-builder]: https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle/blob/master/Menu/DefaultMenuBuilder.php

[create-builder]: https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#method-a-the-easy-way-yay
[create-service-builder]: https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/menu_service.md
[extend-builder]: https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle/blob/master/Menu/AdmingeneratorMenuBuilder.php
