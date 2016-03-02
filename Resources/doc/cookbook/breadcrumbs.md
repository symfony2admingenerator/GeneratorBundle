# Breadcrumbs

[Go back to Table of contents][back-to-index]

-----

It is possible to add breadcrumbs to your admin generator when following this cookbook article.

![Breadcrumbs preview](../img/cookbook-breadcrumbs.png)

### 1. Install suggested breadcrumb bundle
Use composer to install `"cnerta/breadcrumb-bundle": "^2.0"` and add the following to your configuration:

```yaml
cnerta_breadcrumb:
    twig:
        template:	breadcrumbs.html.twig
```

### 2. Create your personal breadcrumbs template

You can customize the example below if needed. The file should be located in `app/Resources/views/breadcrumbs.html.twig`:
```twig
{% block root %}
  {% if items|length > 1 %}
    {% spaceless %}
      <ol id="breadcrumbs" class="breadcrumb hidden-xs hidden-print" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
  	  {% for item in items %}
          {% if loop.last %}{%set itemClass = "active"%}{% endif %}
          <li{% if itemClass is defined and itemClass|length %} class="{{ itemClass }}"{% endif %}{% if not(loop.first) %} itemprop="child"{% endif %}>
            {% if item.uri and not loop.last %}<a href="{{ item.uri }}" itemprop="url"{% if linkRel is defined and linkRel|length %} rel="{{ linkRel }}"{% endif %}>{% endif %}
            {% set icon = item.item.getExtra('icon') %}
            {% if icon is defined and icon is not empty and not loop.last %}<i class="{{ icon }}"></i>{% endif %}
            <span itemprop="title">{{ item.label }}</span>
            {% if item.uri and not loop.last %}</a>{% endif %}
  		</li>
  	  {% endfor %}
      </ol>
    {% endspaceless %}
  {% endif %}
{% endblock root %}
```

### 4. Update the admingenerator layout
```twig
{% block page_header %}
  <section class="content-header">
    {% block page_header_content '' %}
    {% block page_header_breadcrumbs %}{{ cnerta_breadcrumb_render('AppBundle:Builder:breadcrumbMenu') }}{% endblock page_header_breadcrumbs %}
  </section>
{% endblock page_header %}
```

### 5. Building the breadcrumbbs tree

Add the following code to your menu builder to build the breadcrumbs tree (`src/AppBundle/Menu/Builder.php`):
```php
    public function breadcrumbMenu(FactoryInterface $factory, array $options)
    {
        $primaryKey = $this->container->get('request')->get('pk', $this->container->get('request')->get('id', 1));
        $menu = $factory
            ->createItem('main', array('label' => 'homepage', 'route' => 'homepage'))
            ->setExtra('icon', 'fa fa-dashboard')
            ->setExtra('translation_domain', 'messages');
        
        $login = $menu
            ->addChild('fos_user_security_login', array('route' => 'fos_user_security_login'))
            ->setExtra('translation_domain', 'AdmingeneratorUserBundle')
            ->addChild('fos_user_resetting_request', array('route' => 'fos_user_resetting_request',
                'extras' => array(
                    'routes' => array('fos_user_resetting_send_email', 'fos_user_resetting_check_email')), 
                ))
            ->setExtra('translation_domain', 'AdmingeneratorUserBundle');
        $login
            ->addChild('help.title', array('route' => 'help'))
            ->setExtra('translation_domain', 'messages');

        // $this->addAdminGeneratorRoutesForBreadcrumbs($menu, $primaryKey, 'AppBundle_Employee', 'employee');
        // $this->addAdminGeneratorRoutesForBreadcrumbs($menu, $primaryKey, 'AppBundle_Regional_center', 'regional.center', 'regional', true);

        return $menu;
    }
```

It is also possible to generate breadcrumbs for s2a controllers automatically via the `addAdminGeneratorRoutesForBreadcrumbs` function call from the file above. Add the following and uncomment the call in the code above.

```php
    /**
     * Add breadcrumbs for AdminGenerated module.
     *
     * @param ItemInterface $menu
     * @param int           $primaryKey
     * @param string        $routePrefix
     * @param string        $translation_prefix
     * @param string        $translation_catalog
     * @param bool          $edit_under_show
     * @param string        $icon
     *
     * @return ItemInterface
     */
    private function addAdminGeneratorRoutesForBreadcrumbs(ItemInterface $menu, $primaryKey, $routePrefix, $translation_prefix, $translation_catalog = 'messages', $edit_under_show = false, $icon = null)
    {
        $submenu = $menu
            ->addChild($translation_prefix.'.list.title', array('route' => $routePrefix.'_list'))
            ->setExtra('translation_domain', $translation_catalog);

        if ($icon) {
            $submenu->setExtra('icon', 'fa fa-'.$icon);
        }

        $submenu->addChild($translation_prefix.'.new.title', array('route' => $routePrefix.'_new',
            'extras' => array('routes' => array($routePrefix.'_create')), ))
                ->setExtra('translation_domain', $translation_catalog);
        $showmenu = $submenu->addChild($translation_prefix.'.show.title', array('route' => $routePrefix.'_show',
            'routeParameters' => array('pk' => $primaryKey), ))
                ->setExtra('translation_domain', $translation_catalog);

        $mymenu = $edit_under_show ? $showmenu : $submenu;

        $mymenu->addChild(
            $translation_prefix.'.edit.title',
            array(
                'route' => $routePrefix.'_edit',
                'routeParameters' => array('pk' => $primaryKey),
                'extras' => array(
                    'routes' => array($routePrefix.'_update'),
                    'routesParameters' => array('pk' => $primaryKey),
                ),
            )
        )
        ->setExtra('translation_domain', $translation_catalog);

        return $submenu;
    }
```

Many thanks to @ksn135 for this cookbook article.

[back-to-index]: ../documentation.md
