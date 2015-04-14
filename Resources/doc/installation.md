# Installation
---------------------------------------

[go back to Table of contents][back-to-index]


### 1. Update `composer.json`:

Run following commands:

* `composer require symfony2admingenerator/generator-bundle`

* `composer update symfony2admingenerator/generator-bundle`

> **Note:** If you're getting **no matching package found** error then you must also add `"minimum-stability": "dev"` to your **composer.json** file.
    
### 2. Enable bundles

Admin Generator deppends on:
 
 * KnpMenuBundle
 * WhiteOctoberPagerfantaBundle
 * JMSSecurityExtraBundle

> **Note:** there are also some optional dependencies, each is described in corresponding feature`s doc. This guide describes only the minimal-setup. 

Enable Admin Generator and its dependencies in your `app/AppKernel.php`:

```php
<?php 
public function registerBundles()
{
    $bundles = array(
        // ...
        new JMS\AopBundle\JMSAopBundle(),
        new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
        new JMS\DiExtraBundle\JMSDiExtraBundle($this),
        new Admingenerator\GeneratorBundle\AdmingeneratorGeneratorBundle(),
        new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
    );
}
```

You also need to configure the JMS Security Extra Bundle:

```yaml
jms_security_extra:
    # Enables expression language
    expressions: true

```

### 3. Basic configuration

Choose your model manager and choose basic admingenerator template - with or without assetic. -- add following lines to `app/config/config.yml`:

```yaml
admingenerator_generator:
    # choose  and enable at least one
    use_propel:           true
    use_doctrine_orm:     true
    use_doctrine_odm:     false
    
    # choose and uncomment only one
#    base_admin_template: AdmingeneratorGeneratorBundle::base.html.twig
#    base_admin_template: AdmingeneratorGeneratorBundle::base_uncompressed.html.twig
```

### (optional) Configure Assetic to use UglifyCSS and UglifyJS

By default, the `base.html.twig` uses UglifyCSS and UglifyJS to minify assets and combine them into one file (less HTTP requests).

In order to properly install and configure UglifyCSS and UglifyJS follow [this article](http://symfony.com/doc/current/cookbook/assetic/uglifyjs.html)

> See also [Asset Management](http://symfony.com/doc/current/cookbook/assetic/asset_management.html) cookbook entry.

### 4. Install assets

The Admin Generator requires [NodeJS](http://nodejs.org/) and [bower](http://bower.io/) package to download its assets dependencies (jQuery, Twitter Bootstrap and so on).
Make sure `bower` is available through your PATH environment variable, then run the following command:

`php app/console admin:assets-install`

Assets will be downloaded to the `admin` directory into the root `web` directory. 

### (Optional) Dump assets

If you're useing assetic for asset management dump your assets by running:

`php app/console assetic:dump`

### 5. Specify routes

#### Dashboard route (Optional)
By default brand text ("Dashboard") is disabled. To link it with your Dashboard add `dashboard_route` under `admingenerator_generator` in your `app/config/config.yml`:

```yaml
admingenerator_generator:
    dashboard_route:     MyDashboard_path
```

[back-to-index]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/documentation.md
