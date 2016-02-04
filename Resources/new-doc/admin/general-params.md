# General builder params

[go back to Table of contents][back-to-index]

-----

The default params for a new generator are as follows:
```yaml
generator: {{ generator }}
params:
    model: {{ namespace }}\{{ model_folder }}\{{ model_name }}
    namespace_prefix: {{ namespace_prefix }}
    bundle_name: {{ bundle_name }}
    concurrency_lock: ~
    i18n_catalog: Admin
    credentials: ~
    pk_requirement: ~
    custom_blocks: ~
    fields: ~
    object_actions:
        delete: ~
    batch_actions:
        delete: ~
	actions: ~
```

They can be customized on a per admin basis, according to your needs.

### Generator
`generator` __default__: `admingenerator.generator.{{generator}}` __type__: `string`

The generator is the most important part of the general config. It indicates the service that needs to be used for generator this particular admin page. By default, is is the generator service provided by this bundle for the model manager entered during admin creation.

### Model
`model` __default__: `{{ namespace }}\{{ model_folder }}\{{ model_name }}` __type__: `string`

The model that this admin manages. Use the fully qualified model name.

### Bundle
`namespace_prefix` __default__: `{{ namespace_prefix }}` __type__: `string`
`bundle_name` __default__: `{{ bundle_name }}` __type__: `string`

The `namespace_prefix` and `bundle_name` together form the fully qualified bundle name. For example, in the case of the bundle `Acme/AdminBundle`, the `namespace_prefix` would be `Acme` and the `bundle_name` would be `AdminBundle`.

### Concurrency lock
`concurrency_lock` __default__: `false` __type__: `boolean`

To protect your models edit action against concurrent modifications during long-running business transactions this parameter can be used. If enabled, before updating actually updating the object, this bundle will check if there were any modifications by comparing object versions. Simply set it to true and make sure that your model contains a version field.

##### Versionable field for Doctrine ORM/ODM

Add the `version` property to your model, with the correct annotation.

```php
class Article
{
    /**
     * @ORM\Version()
     * @ORM\Column(type="integer")
     */
    private $version;

    public function getVersion(){
        return $this->version;
    }
}
```

##### Versionable field for Propel
In the schema.xml, use the `<behavior>` tag to add the versionable behavior to a table:

```xml
<table name="article">
    <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
    <column name="title" type="VARCHAR" required="true" />
    <behavior name="versionable" />
</table>
```

### Translation domain
`i18n_catalog` __default__: `Admin` __type__: `string`

Provide the tranlation catalogue that needs to be used to translate the text.

### Credentials
`credentials` __default__: `~` __type__: `string`

By default, there are no credentials required to view every page of this particular admin. To check for a specific credential, just enter is here. For more documenation about credentials, check our [security documentation][security-doc].

> __NOTE__ Credentials given here are valid for the whole admin, but can be overridden in specific builders or even specific fields.

### Primary key requirement
`pk_requirement` __default__: `~` __type__: `string`

By default the generated routes are quite greedy, which can be solved by adding primary key requirements to those routes. Simply add the requirements as wanted by the normal convention. Set it for example to `\d+` to only allow numeric primary key values in every generated route using a primary key.

### Custom show blocks
`custom_blocks` __default__: `~` __type__: `string`

For displaying values (such as in the list view or simply the show view) by default this bundle uses the database type to find a suiting show format. Currently, the following types are supported by default (in this order):
* _custom_: Custom view block as specified in the `customView` yaml field configuration.
* _boolean_: Shows a cross (false) or check (true).
* _date_: Prints the date. Will be formatted according to your field configuration.
* _datetime_: Prints the date & time. Again according to your field configuration.
* _decimal_: Prints the number in decimal notation
* _money_: Prints the number with a currency sign
* _collection_: Prints the collection
* _other_: Simply prints the value as returned by the getter method.


##### Custom views
When you want to use a custom show block, the value of this parameter must be set to the twig file containing the block you want to use. For example:

```yaml
custom_blocks: AcmeAcmeBundle:Form:custom_blocks.html.twig
```

In the field configuration itself you will need to specify the block you want to use, for example:
```yaml
fields:
  id:
    label: Default
    customView: event_id
```

In this example, the custom blocks template must contain the following block:
```twig
{% block column_event_id %}
  {% spaceless %}
    {% if field_value == 1 %}
      <i class="fa fa-asterisk"></i>
    {% endif %}
  {% endspaceless %}
{% endblock %}
```

> **Note** You will need to specify a block with `column_`+`customView` value in the given `custom_blocks` twig file.

> **Note** The `custom_blocks` file can contain multiple blocks.

### Fields
`fields` __default__: `~` __type__: `array`

The fields parameter can be used to configure the fields on a global level. THe configuration entered here will be used by the builders, which can overwrite specific parts.

For more information about configuring field, see our [field configuration documentation][field-doc].

### Actions
`object_actions` __default__: `~` __type__: `array`
`batch_actions` __default__: `~` __type__: `array`
`actions` __default__: `~` __type__: `array`

This bundle provides some configurable default actions, but custom actions can also be defined. There are three types of actions:

  1. Object actions: Actions which are applied to a specific object, identified by a primary key
  2. Batch actions: Actions which are applied on a selection of object.
  3. Actions: Actions which are possible on a specific object when viewing or editing it.

More information on actions can be found in the [action configuration documentation][actions-doc].

[back-to-index]: ../documentation.md
[security-doc]: security.md
[field-doc]: fields.md
[actions-doc]: actions.md