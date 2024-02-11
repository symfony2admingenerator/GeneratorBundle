# Base installation
---------------------------------------

[Go back to Table of contents][back-to-index]


### 1. Default configuration file

We do not have a published flex recipe, so make sure to create a configuration file first.
Choose your model manager (Doctrine, Doctrine ODM or Propel) and choose if you want to use Assetic.
Create `config/packages/admingenerator_generator.yaml` and add the following:

```yaml
admingenerator_generator:
    # choose and enable at least one
    use_propel:           false
    use_doctrine_orm:     true
    use_doctrine_odm:     false

    # add this line if you want to use assetic
    base_admin_template: @AdmingeneratorGenerator/base_uncompressed.html.twig
```

### 2. Update `composer.json`:

Run following commands:

* `composer require symfony2admingenerator/generator-bundle`

### 3. Enable bundles

This bundle depends on:

 * BabDevPagerfantaBundle

> **Note:** there are also some optional dependencies, each is described in corresponding feature`s doc. This guide describes only the minimal-setup.

Enable Admin Generator and its dependencies in your `config/bundles.php`. (when using flex, this has been done for you):

```php
<?php
    BabDev\PagerfantaBundle\BabDevPagerfantaBundle::class => ['all' => true],
    Admingenerator\GeneratorBundle\AdmingeneratorGeneratorBundle::class => ['all' => true],
```

## 4. Configure class loader

Required to prevent cache issues.

> **Note:** This is not needed when using the `generate_base_in_project_dir` setting.

Place the following in your frontend controller (`public/index.php`) and your console binary (`bin/console`)
to ensure the `AdminGenerated` namespace is loaded! Make sure to place it directly after the kernel instantiation.

```php
use Admingenerator\GeneratorBundle\ClassLoader\AdmingeneratedClassLoader;

// Preload the admin generator namespace to prevent class load errors when checking whether the cache is fresh
AdmingeneratedClassLoader::initAdmingeneratorClassLoader($kernel->getCacheDir());
```

### (optional) JMS Security extra

If you want to use the JMS Security Extra expressions, make sure to 
[install the bundle](http://jmsyst.com/bundles/JMSSecurityExtraBundle/master/installation#using-composer-recommended)
and enable it in the config:

```yaml
jms_security_extra:
    # Enables expression language
    expressions: true

admingenerator_generator:
    use_jms_security: true
```

### (optional) Configure Assetic to use UglifyCSS and UglifyJS

By default, the `base.html.twig` uses UglifyCSS and UglifyJS to minify assets and combine them into one file (less HTTP requests).

In order to properly install and configure UglifyCSS and UglifyJS follow [this article](http://symfony.com/doc/current/cookbook/assetic/uglifyjs.html)

> See also [Asset Management](http://symfony.com/doc/current/cookbook/assetic/asset_management.html) cookbook entry.

### 4. (optional) Install assets

The Admin Generator requires [NodeJS](http://nodejs.org/) and [bower](http://bower.io/) package to download its assets dependencies (jQuery, Twitter Bootstrap and so on).
Make sure `bower` is available through your PATH environment variable, then run the following command:

`php app/console admin:assets-install`

Assets will be downloaded to the `admin` directory into the root `web` directory. 

### (optional) Dump assets

If you're using assetic for asset management, dump your assets by running:

`php app/console assetic:dump`

### (optional) Specify Dashboard route

By default, the brand text ("Dashboard") is disabled. To link it with your Dashboard add `dashboard_route` under `admingenerator_generator` in your `app/config/config.yml`:

```yaml
admingenerator_generator:
    dashboard_route: MyDashboard_path
```

[back-to-index]: ../documentation.md
