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
        ->createItem('main', array('label' => 'Главная', 'route' => 'homepage'))
        ->setExtra('icon', 'fa fa-dashboard');
    $login = $menu->addChild('Идентификация', array('route' => 'fos_user_security_login'))
        ->addChild('Восстановление пароля', array('route' => 'fos_user_resetting_request',
            'extras' => array('routes' => array('fos_user_resetting_send_email', 'fos_user_resetting_check_email')), ));
    $login->addChild('Получение доступа в систему ?', array('route' => 'help'));

    $user = $menu->addChild('Список сотрудников', array('route' => 'AppBundle_Employee_list',
        'extras' => array('routes' => array('AppBundle_Employee_create')), ));
    $user->addChild('Добавление нового сотрудника', array('route' => 'AppBundle_Employee_new'));
    $user->addChild('Карточка сотрудника', array('route' => 'AppBundle_Employee_show',
            'routeParameters' => array('pk' => $primaryKey), ))
        ->addChild('Редактирование сотрудника', array('route' => 'AppBundle_Employee_edit',
            'routeParameters' => array('pk' => $primaryKey), 'extra' => array(
                'routes' => array('AppBundle_Employee_update'),
                'routesParameters' => array('pk' => $primaryKey), ),
            ));

    $profile = $user->addChild('Просмотр профиля', array('route' => 'profile'));
    $profile->addChild('Параметры профиля', array('route' => 'fos_user_profile_edit'));

    // $this->addAdminGeneratorRoutesForBreadcrumbs($menu, $primaryKey, 'AppBundle_Employee',
    //     'Список сотрудников', 'Добавление нового сотрудника',
    //     'Карточка сотрудника', 'Правка сотрудника', true, 'users'
    // );

    return $menu;
}
```

It is also possible to generate breadcrumbs for s2a controllers automatically via the `addAdminGeneratorRoutesForBreadcrumbs` function call from the file above. Add the following and uncomment the call in the code above.

```php
/**
 * Add breadcrumbs for AdminGenerated module.
 *
 * @param ItemInterface $menu
 * @param int             $primaryKey
 * @param string        $routePrefix
 * @param string        $listLabel
 * @param string        $newLabel
 * @param string        $showLabel
 * @param string        $editLabel
 * @param boolean    $edit_under_show
 * @param string        $icon
 *
 * @return ItemInterface
 */
private function addAdminGeneratorRoutesForBreadcrumbs(ItemInterface $menu, $primaryKey, $routePrefix, $listLabel, $newLabel, $showLabel, $editLabel, $edit_under_show = false, $icon = null)
{
    $submenu = $menu
        ->addChild($listLabel, array('route' => $routePrefix.'_list'));

    if ($icon) {
        $submenu->setExtra('icon', 'fa fa-'.$icon);
    }

    $submenu->addChild($newLabel, array('route' => $routePrefix.'_create'));
    $submenu->addChild($newLabel.' ', array('route' => $routePrefix.'_new'));
    $showmenu = $submenu->addChild($showLabel, array('route' => $routePrefix.'_show',
            'routeParameters' => array('pk' => $primaryKey), ));

    if ($edit_under_show) {
        $showmenu->addChild(
            $editLabel,
            array(
                'route' => $routePrefix.'_edit',
                'routeParameters' => array('pk' => $primaryKey),
            )
        );
        $showmenu->addChild(
            $editLabel.' ',
            array(
                'route' => $routePrefix.'_update',
                'routeParameters' => array('pk' => $primaryKey),
            )
        );
    } else {
        $submenu->addChild(
            $editLabel,
            array(
                'route' => $routePrefix.'_edit',
                'routeParameters' => array('pk' => $primaryKey),
            )
        );
        $submenu->addChild(
            $editLabel.' ',
            array(
                'route' => $routePrefix.'_update',
                'routeParameters' => array('pk' => $primaryKey),
            )
        );
    }

    return $menu;
}
```

Many thanks to @ksn135 for this cookbook article.

[back-to-index]: ../documentation.md
