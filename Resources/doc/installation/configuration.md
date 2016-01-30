# Configuration
---------------------------------------

[go back to Table of contents][back-to-index]

[back-to-index]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/documentation.md#1-installation

### 1. Global configurations

_TODO_

### 2. Cache configuration

`generator_cache`: __default__: `null` __type__: `string` (service name extending `Doctrine\Common\Cache\CacheProvider`)

By default, for each request matching an Admingenerated controller, the `ControllerListener` will iterate over
the filesystem to find which right generator.yml and the right `Generator` have to be used to build generated
files. This process could take some time. Thanks to this configuration, you can define a cache provider to bypass
this process once all files are generated. The service name defined here need to extend the class
`Doctrine\Common\Cache\CacheProvider`.

Example:

```yaml
services:
    global_cache.provider:
        class: %doctrine.orm.cache.apc.class% # This class comes from Doctrine, you can create your own
        public: false
        calls:
            - [ setNamespace, [ 'my_namespace' ] ]

admingenerator_generator:
    generator_cache: global_cache.provider

```

### 3. Twig section

Default configuration is:

```yaml
admingenerator_generator:
    twig:
        use_form_resources: true
        use_localized_date: false
        date_format: Y-m-d
        datetime_format: Y-m-d H:i:s
        localized_date_format: medium
        localized_datetime_format: medium
        number_format:
            decimal: 0
            decimal_point: .
            thousand_separator: ,
```

`use_form_resources`

By default, `AdmingeneratorGeneratorBundle` adds its own form theme to your application based on files 
`AdmingeneratorGeneratorBundle:Form:fields.html.twig` and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig`. 
Depending on value of `admingenerator_generator.twig.use_form_resources` parameter and `twig.form.resources` one, 
you can modify this behavior:

* if `admingenerator_generator.twig.use_form_resources` is false, nothing will be changed to `twig.form.resources` value;
* if `admingenerator_generator.twig.use_form_resources` is true and `twig.form.resources` doesn't contain 
`AdmingeneratorGeneratorBundle:Form:fields.html.twig`, resources `AdmingeneratorGeneratorBundle:Form:fields.html.twig` 
and `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` will be into `twig.form.resources` right after 
`form_div_layout.html.twig`. If `form_div_layout.html.twig` is not in `twig.form.resources` values will be unshifted;
* if `AdmingeneratorGeneratorBundle:Form:fields.html.twig` is already in `twig.form.resources` nothing will be changed;

This permits you to control how `AdmingeneratorGeneratorBundle` modify form theming in your application. If you want to 
use another bundle for form theming (like `MopaBoostrapBundle`) you should probably define this parameter as false.

> **Note:** take care that if you are in this case, don't forget to add `AdmingeneratorGeneratorBundle:Form:widgets.html.twig` 
if you don't provide your own implementation.

*To complete*

### 4. Full configuration

```yaml
admingenerator_generator:
    ## Global
    use_doctrine_orm: false
    use_doctrine_odm: false
    use_propel: false
    use_jms_security: false
    overwrite_if_exists: false
    guess_required: true
    default_required: false
    throw_exceptions: false
    base_admin_template: AdmingeneratorGeneratorBundle::base.html.twig
    dashboard_route: ~
    login_route: ~
    logout_route: ~
    exit_route: ~
    generator_cache: ~
    ## Twig and Templates
    twig:
        use_form_resources: true
        use_localized_date: false
        date_format: Y-m-d
        datetime_format: Y-m-d H:i:s
        localized_date_format: medium
        localized_datetime_format: medium
        number_format:
            decimal: 0
            decimal_point: .
            thousand_separator: ,
    templates_dirs: []
    stylesheets: [] # array of {path: path_to_stylesheet, media: all}
    javascripts: [] # array of {path: path_to_javascript, route: route_name, routeparams: [value1, value2]}
    form_types:
        doctrine_orm:
            datetime:     Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            vardatetime:  Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            datetimetz:   Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            date:         Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            time:         Symfony\Component\Form\Extension\Core\Type\TimeType 
            decimal:      Symfony\Component\Form\Extension\Core\Type\NumberType 
            float:        Symfony\Component\Form\Extension\Core\Type\NumberType 
            integer:      Symfony\Component\Form\Extension\Core\Type\IntegerType 
            bigint:       Symfony\Component\Form\Extension\Core\Type\IntegerType 
            smallint:     Symfony\Component\Form\Extension\Core\Type\IntegerType 
            string:       Symfony\Component\Form\Extension\Core\Type\TextType 
            text:         Symfony\Component\Form\Extension\Core\Type\TextareaType
            entity:       Symfony\Bridge\Doctrine\Form\Type\EntityType 
            collection:   Symfony\Component\Form\Extension\Core\Type\CollectionType 
            array:        Symfony\Component\Form\Extension\Core\Type\CollectionType 
            boolean:      Symfony\Component\Form\Extension\Core\Type\CheckboxType 
        doctrine_odm:
            datetime:     Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            timestamp:    Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            vardatetime:  Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            datetimetz:   Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            date:         Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            time:         Symfony\Component\Form\Extension\Core\Type\TimeType 
            decimal:      Symfony\Component\Form\Extension\Core\Type\NumberType 
            float:        Symfony\Component\Form\Extension\Core\Type\NumberType 
            int:          Symfony\Component\Form\Extension\Core\Type\IntegerType 
            integer:      Symfony\Component\Form\Extension\Core\Type\IntegerType 
            int_id:       Symfony\Component\Form\Extension\Core\Type\IntegerType 
            bigint:       Symfony\Component\Form\Extension\Core\Type\IntegerType 
            smallint:     Symfony\Component\Form\Extension\Core\Type\IntegerType 
            id:           Symfony\Component\Form\Extension\Core\Type\TextType 
            custom_id:    Symfony\Component\Form\Extension\Core\Type\TextType 
            string:       Symfony\Component\Form\Extension\Core\Type\TextType 
            text:         Symfony\Component\Form\Extension\Core\Type\TextareaType 
            document:     Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType 
            collection:   Symfony\Component\Form\Extension\Core\Type\CollectionType 
            hash:         Symfony\Component\Form\Extension\Core\Type\CollectionType 
            boolean:      Symfony\Component\Form\Extension\Core\Type\CheckboxType 
        propel:
            TIMESTAMP:    Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            BU_TIMESTAMP: Symfony\Component\Form\Extension\Core\Type\DateTimeType 
            DATE:         Symfony\Component\Form\Extension\Core\Type\DateType 
            BU_DATE:      Symfony\Component\Form\Extension\Core\Type\DateType 
            TIME:         Symfony\Component\Form\Extension\Core\Type\TimeType 
            FLOAT:        Symfony\Component\Form\Extension\Core\Type\NumberType 
            REAL:         Symfony\Component\Form\Extension\Core\Type\NumberType 
            DOUBLE:       Symfony\Component\Form\Extension\Core\Type\NumberType 
            DECIMAL:      Symfony\Component\Form\Extension\Core\Type\NumberType 
            TINYINT:      Symfony\Component\Form\Extension\Core\Type\IntegerType 
            SMALLINT:     Symfony\Component\Form\Extension\Core\Type\IntegerType 
            INTEGER:      Symfony\Component\Form\Extension\Core\Type\IntegerType 
            BIGINT:       Symfony\Component\Form\Extension\Core\Type\IntegerType 
            NUMERIC:      Symfony\Component\Form\Extension\Core\Type\IntegerType 
            CHAR:         Symfony\Component\Form\Extension\Core\Type\TextType 
            VARCHAR:      Symfony\Component\Form\Extension\Core\Type\TextType 
            LONGVARCHAR:  Symfony\Component\Form\Extension\Core\Type\TextareaType 
            BLOB:         Symfony\Component\Form\Extension\Core\Type\TextareaType 
            CLOB:         Symfony\Component\Form\Extension\Core\Type\TextareaType 
            CLOB_EMU:     Symfony\Component\Form\Extension\Core\Type\TextareaType 
            model:        Symfony\Bridge\Propel1\Form\Type\ModelType 
            collection:   Symfony\Component\Form\Extension\Core\Type\CollectionType 
            PHP_ARRAY:    Symfony\Component\Form\Extension\Core\Type\CollectionType 
            ENUM:         Symfony\Component\Form\Extension\Core\Type\ChoiceType 
            BOOLEAN:      Symfony\Component\Form\Extension\Core\Type\CheckboxType 
            BOOLEAN_EMU:  Symfony\Component\Form\Extension\Core\Type\CheckboxType 
    filter_types:
        doctrine_orm:
            datetime:      Symfony\Component\Form\Extension\Core\Type\DateTimeType
            vardatetime:   Symfony\Component\Form\Extension\Core\Type\DateTimeType
            datetimetz:    Symfony\Component\Form\Extension\Core\Type\DateTimeType
            date:          Symfony\Component\Form\Extension\Core\Type\DateType
            time:          Symfony\Component\Form\Extension\Core\Type\TimeType
            decimal:       Symfony\Component\Form\Extension\Core\Type\NumberType
            float:         Symfony\Component\Form\Extension\Core\Type\NumberType
            integer:       Symfony\Component\Form\Extension\Core\Type\NumberType
            bigint:        Symfony\Component\Form\Extension\Core\Type\NumberType
            smallint:      Symfony\Component\Form\Extension\Core\Type\NumberType
            string:        Symfony\Component\Form\Extension\Core\Type\TextType
            entity:        Symfony\Bridge\Doctrine\Form\Type\EntityType
            collection:    Symfony\Component\Form\Extension\Core\Type\CollectionType
            array:         Symfony\Component\Form\Extension\Core\Type\TextType
            boolean:       Symfony\Component\Form\Extension\Core\Type\ChoiceType
        doctrine_odm:
            datetime:      Symfony\Component\Form\Extension\Core\Type\DateTimeType
            timestamp:     Symfony\Component\Form\Extension\Core\Type\DateTimeType
            vardatetime:   Symfony\Component\Form\Extension\Core\Type\DateTimeType
            datetimetz:    Symfony\Component\Form\Extension\Core\Type\DateTimeType
            date:          Symfony\Component\Form\Extension\Core\Type\DateType
            time:          Symfony\Component\Form\Extension\Core\Type\TimeType
            decimal:       Symfony\Component\Form\Extension\Core\Type\NumberType
            float:         Symfony\Component\Form\Extension\Core\Type\NumberType
            int:           Symfony\Component\Form\Extension\Core\Type\NumberType
            integer:       Symfony\Component\Form\Extension\Core\Type\NumberType
            int_id:        Symfony\Component\Form\Extension\Core\Type\NumberType
            bigint:        Symfony\Component\Form\Extension\Core\Type\NumberType
            smallint:      Symfony\Component\Form\Extension\Core\Type\NumberType
            id:            Symfony\Component\Form\Extension\Core\Type\TextType
            custom_id:     Symfony\Component\Form\Extension\Core\Type\TextType
            string:        Symfony\Component\Form\Extension\Core\Type\TextType
            text:          Symfony\Component\Form\Extension\Core\Type\TextType
            document:      Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType
            collection:    Symfony\Component\Form\Extension\Core\Type\CollectionType
            hash:          Symfony\Component\Form\Extension\Core\Type\TextType
            boolean:       Symfony\Component\Form\Extension\Core\Type\ChoiceType
        propel:
            TIMESTAMP:     Symfony\Component\Form\Extension\Core\Type\DateTimeType
            BU_TIMESTAMP:  Symfony\Component\Form\Extension\Core\Type\DateTimeType
            DATE:          Symfony\Component\Form\Extension\Core\Type\DateType
            BU_DATE:       Symfony\Component\Form\Extension\Core\Type\DateType
            TIME:          Symfony\Component\Form\Extension\Core\Type\TimeType
            FLOAT:         Symfony\Component\Form\Extension\Core\Type\NumberType
            REAL:          Symfony\Component\Form\Extension\Core\Type\NumberType
            DOUBLE:        Symfony\Component\Form\Extension\Core\Type\NumberType
            DECIMAL:       Symfony\Component\Form\Extension\Core\Type\NumberType
            TINYINT:       Symfony\Component\Form\Extension\Core\Type\NumberType
            SMALLINT:      Symfony\Component\Form\Extension\Core\Type\NumberType
            INTEGER:       Symfony\Component\Form\Extension\Core\Type\NumberType
            BIGINT:        Symfony\Component\Form\Extension\Core\Type\NumberType
            NUMERIC:       Symfony\Component\Form\Extension\Core\Type\NumberType
            CHAR:          Symfony\Component\Form\Extension\Core\Type\TextType
            VARCHAR:       Symfony\Component\Form\Extension\Core\Type\TextType
            LONGVARCHAR:   Symfony\Component\Form\Extension\Core\Type\TextType
            BLOB:          Symfony\Component\Form\Extension\Core\Type\TextType
            CLOB:          Symfony\Component\Form\Extension\Core\Type\TextType
            CLOB_EMU:      Symfony\Component\Form\Extension\Core\Type\TextType
            model:         Symfony\Bridge\Propel1\Form\Type\ModelType
            collection:    Symfony\Component\Form\Extension\Core\Type\CollectionType
            PHP_ARRAY:     Symfony\Component\Form\Extension\Core\Type\TextType
            ENUM:          Symfony\Component\Form\Extension\Core\Type\TextType
            BOOLEAN:       Symfony\Component\Form\Extension\Core\Type\ChoiceType
            BOOLEAN_EMU:   Symfony\Component\Form\Extension\Core\Type\ChoiceType
```
