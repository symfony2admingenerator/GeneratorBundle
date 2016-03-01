# General configuration

[Go back to Table of contents][back-to-index]

-----
### Default configuration
Below the default configuration is given for this bundle.

```yaml
admingenerator_generator:
    use_doctrine_orm: false
    use_doctrine_odm: false
    use_propel: false
    use_jms_security: false
    guess_required: true
    default_required: true
    overwrite_if_exists: false
    throw_exceptions: false
    base_admin_template: 'AdmingeneratorGeneratorBundle::base.html.twig'
    dashboard_route: null
    login_route: null
    logout_route: null
    exit_route: null
    generator_cache: null
    default_action_after_save: edit
    twig:
        use_form_resources: true
        use_localized_date: false
        date_format: Y-m-d
        datetime_format: 'Y-m-d H:i:s'
        localized_date_format: medium
        localized_datetime_format: medium
        number_format:
        	decimal: 0
            decimal_point: .
            thousand_separator: ','
    templates_dirs: {  }
    form_types:
    	doctrine_orm: # Available form types for Doctrine ORM
        doctrine_odm: # Available form types for Doctrine ODM
        propel: { # Available form types for Propel }
    filter_types:
        doctrine_orm: # Available form types for Doctrine ORM
        doctrine_odm: # Available form types for Doctrine ODM
        propel: # Available form types for Propel
    stylesheets: {  }
    javascripts: {  }
```

### Use Doctrine ORM/ODM/Propel
`use_doctrine_orm`: __default__: `false` __type__: `boolean`
`use_doctrine_odm`: __default__: `false` __type__: `boolean`
`use_propel`: __default__: `false` __type__: `boolean`

You must enable one of the model managers to be able to use the bundle. Enable the correct one with a simple `true`.

### Use JMS Security Extra bundle
`use_jms_security`: __default__: `false` __type__: `boolean`

Set this to true to enable the JMS Security bundle to parse the credentials defined in your model configuration.

### Guess required
`guess_required`: __default__: `true` __type__: `boolean`

By default, this bundle guesses if a field is required with your model data. This behavior can be disabled by putting `false` here.

### Default required
`default_required`: __default__: `true` __type__: `boolean`

By default, all fields are required. This configuration only has effect is the guess is disabled.

### Overwrite if exists
`overwrite_if_exists`: __default__: `false` __type__: `boolean`

If set to `true` all files are always regenerated instead of using the already written version if there are no changes.

### Throw exceptions
`throw_exceptions`: __default__: `false` __type__: `boolean`

By default, the admingenerator tries to catch all exceptions during form handling. This results in a general message for the user. If you have a custom error handling system (which for example mails the error or shows a neat 500 error page), you can set this option to `true`. The errors will no longer be catched and will be handler by your custom handler.

__Note__: Exceptions will always be thrown is the parameter `%kernel.debug%` is `true`.

### Base admin template
 `base_admin_template`: __default__: `AdmingeneratorGeneratorBundle::base.html.twig` __type__: `string`

### Routes
`dashboard_route`: __default__: `null` __type__: `string`
`login_route`: __default__: `null` __type__: `string`
`logout_route`: __default__: `null` __type__: `string`
`exit_route`: __default__: `null` __type__: `string`

### Default actions
`default_action_after_save`: __default__: `edit` __type__: `string`

Adjust the default action after the save action has been processed successfully. Can be one of `new`, `edit`, `list`, `show` or a custom name which needs to created in your controller (which is propably not desirable on a global level).

### Twig
`twig`: __type__: `array`

##### General
`twig.use_form_resources`: __default__: `true` __type__: `boolean`
By default, this bundle adds its own form theme to your application based on the files `AdmingeneratorGeneratorBundle:Form:fields.html.twig` and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig`. Depending on the value of `admingenerator_generator.twig.use_form_resources` parameter and `twig.form.resources` one, you can modify this behavior:

* If `admingenerator_generator.twig.use_form_resources` is false, nothing will be changed to the `twig.form.resources` value
* If `admingenerator_generator.twig.use_form_resources` is true and `twig.form.resources` doesn't contain `AdmingeneratorGeneratorBundle:Form:fields.html.twig`, resources `AdmingeneratorGeneratorBundle:Form:fields.html.twig` and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` will be merged into `twig.form.resources` right after `form_div_layout.html.twig`. If `form_div_layout.html.twig` is not in `twig.form.resources` values will be unshifted;
* If `AdmingeneratorGeneratorBundle:Form:fields.html.twig` is already in `twig.form.resources` nothing will be changed;

This permits you to control how this bundle modifies form theming in your application. If you want to use another bundle for form theming (like `MopaBoostrapBundle`) you should probably define this parameter as false.

> **Note:** take care that if you are in this case, don't forget to add `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` if you don't provide your own implementation.


##### Date/time formatting
`twig.use_localized_date`: __default__: `false` __type__: `boolean`

Set this to `true` to enable the `LocalizedDate` filter from twig, instead of using the static format.
> **Note:** these apply only to `list` and `show` builders. These settings have no effect on `edit` and `new` forms.

> **WARNING!** Internally localized date is handled by [IntlDateFormatter class][intl-date-formatter], which uses [ISO 8601][iso-8601] formats instead of PHP date()'s formats. Make sure to set the correct `date_format` and `datetime_format` if you enable localized date!

If `use_localized_date` is enabled, the date(time) field will be rendered as:

```html+django
{{ my_date|localizeddate(localized_date_format, "none", null, null, date_format) }}
{{ my_date|localizeddate(localized_datetime_format, localized_datetime_format, null, null, datetime_format) }}
```

Otherwise the date(time) field will be rendered as:

```html+django
{{ my_date|date(date_format) }}
{{ my_date|date(datetime_format) }}
```

Where `date_format`/`datetime_format` is equal to `format` option for that field (if defined) or will fallback to `admingenerator_generator.twig.date_format`/`admingenerator_generator.twig.datetime_format` setting.

-----

`twig.date_format`: __default__: `Y-m-d` __type__: `string`
`twig.datetime_format`: __default__: `Y-m-d H:i:s` __type__: `string`

If `use_localized_date` is `false`, use formats for PHP's `date()` function, otherwise use [ISO 8601][iso-8601] formats.

-----

`twig.localized_date_format`: __default__: `medium` __type__: `string`
`twig.localized_datetime_format`: __default__: `medium` __type__: `string`

Internally, we're using [Twig Intl Extension][twig-intl-ext]. Possible options are:

```php
<?php
array(
  'none'    => IntlDateFormatter::NONE,
  'short'   => IntlDateFormatter::SHORT,
  'medium'  => IntlDateFormatter::MEDIUM,
  'long'    => IntlDateFormatter::LONG,
  'full'    => IntlDateFormatter::FULL,
)
```

##### Number formatting
`twig.number_format.decimal`: __default__: `0` __type__: `string`
`twig.number_format.decimal_point`: __default__: `.` __type__: `string`
`twig.number_format.thousand_separator`: __default__: `,` __type__: `string`

Configure the default representation of numbers with these configuration parameters.

### Template dirs
`template_dirs` __default__: `{}`, __type__: `array`

Sometimes, you may want to extend/overwrite some of the generator templates in the Resources/templates dir. This is quite easy, but you will have to do the following steps:

1. First, you will need to add the template you will be using to the admingenerator config
```yaml
templates_dirs: [ "%kernel.root_dir%/../app/Resources/AdmingeneratorGeneratorBundle/templates" ]
```

2. Keep in mind that you will at least need **one dir** in the previous specified template directory, namely of the model manager used. This will be one of `Doctrine`, `DoctrineODM` or `Propel`.
> **WARNING!** Without this directory, the specified template directory will not be used for extending/overwriting any of the templates, even in the CommonAdmin dir.

3. Be free to extend/overwrite any template in the Resources/templates dir!

Please note that your own templates might need adjustment when new releases of the bundle arrive. So if anything breaks after an update and you're using custom templates, please check those first!

### Generator cache
`generator_cache`: __default__: `null` __type__: `string` (service name extending `Doctrine\Common\Cache\CacheProvider`)

By default, for each request matching an Admingenerated controller, the `ControllerListener` will iterate over
the filesystem to find which right generator.yml and the right `Generator` have to be used to build generated
files. This process could take some time. Thanks to this configuration, you can define a cache provider to bypass
this process once all files are generated. The service name defined here need to extend the class
`Doctrine\Common\Cache\CacheProvider`.

Example:

```yaml
services:
    admingenerator.provider:
        class: %doctrine.orm.cache.apc.class% # This class comes from Doctrine, you can create your own
        public: false
        calls:
            - [ setNamespace, [ 'my_namespace' ] ]

admingenerator_generator:
    generator_cache: admingenerator.provider

```


### Form/Filter types
To be able to guess the correct form type for the attribute of your model as abstracted by your model manager, the bundle contains sensible defaults for most fields. They can be found on the following pages:

* [Doctrine ORM][form-type-orm]
* [Doctrine ODM][form-type-odm]
* [Propel][form-type-propel]

Note that the values are different for the new/edit forms and the filter forms.

To add or overwrite a value of the default guess, simply add the following to your `config.yml`:
```yaml
form_types:
    doctrine_orm:
        phone_number: text
```

This add the field type `phone_number`, which will be rendered with a normal `text` form type (a textbox). The configuration you add will be merged with the default configuration, such that you only need to add that you want to add or change.

### Stylesheets/javascripts

You can use the `stylesheets` and `javascripts` config values to add specific js/css files to your admin generator pages. Simply add them as shown in the example below:

```yaml
stylesheets:
    - { path: path_to_stylesheet, media: all }
javascripts:
    - { path: path_to_javascript, route: route_name, routeparams: [value1, value2] }
```

[back-to-index]: ../documentation.md
[form-type-orm]: form_types/doctrine_orm.md
[form-type-odm]: form_types/doctrine_odm.md
[form-type-propel]: form_types/propel.md

[intl-date-formatter]: http://www.php.net/manual/en/intldateformatter.format.php
[iso-8601]: http://framework.zend.com/manual/1.12/en/zend.date.constants.html#zend.date.constants.selfdefinedformats
[twig-intl-ext]: https://github.com/fabpot/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Intl.php