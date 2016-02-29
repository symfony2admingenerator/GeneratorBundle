# Form field configuration

[go back to Table of contents][back-to-index]

-----

Form fields usually are based on the model, where every attribute will become a field. By default, this bundle guesses the required field type based on the parameter types (see all [available form types][form-types]). This default can be overriden at any time, at any part.

### Configuration placement
Form fields can be configured on several places in your admin configuration file. Every builder can have it's own form field configuration, but it is also possible to set the default configuration in the `params` section. While the configuration in the `params` section is global, it is possible to overwrite specific parts in the builder  configuration.

```yaml
params:
	fields: # Global configuration
        <options>
builder:
	list:
    	fields: # Filter form specific configuration
        	<options>
    new:
    	fields: # Create form specific configuration
        	<options>
    edit:
        fields: # Edit form specific configuration
        	<options>
```

### Field configuration

The field configuration in the admin generator is similar to the field configuration as you would do with any Symfony form. Therefor, make sure to know how to [do field configuration for Symfony Forms][sf-form-doc].

Every field will be preconfigured by the guesser, which will guess the form type based on the model (see the [form types][form-types]). With the form type known, default required parameters will also be guessed. Per field, a lot of options can be set:

* [addFilterOptions](#add-filter-options)
* [addFormOptions](#add-form-options)
* [credentials](#credentials)
* [customView](#custom-views)
* [dbFormat](#database-format)
* [dbType](#database-type)
* [extras](#extras)
* [filterable](#filterable)
* [filterOn](#filter-on)
* [filterOptions](#filter-options)
* [filtersCredentials](#filter-credentials)
* [filterType](#filter-type)
* [formOptions](#form-options)
* [formType](#form-type)
* [getter](#getter)
* [gridClass](#grid-class)
* [help](#help)
* [label](#label)
* [localizedDateFormat](#localized-date-format)
* [localizedTimeFormat](#localized-time-format)
* [manyToMany](#many-to-many)
* [primaryKey](#primary-key)
* [sortable](#sortable)
* [sortOn](#sort-on)
* [sortType](#sort-type)

##### Add filter options
`addFilterOptions` __type__: `array`

> **Note** By default, the formOptions are automatically filled with sensible defaults. Only use these if you want to override specific options

With `addFilterOptions` you can set/overwrite specific Symfony form options for the filter form. More information can be found at [addFormOptions](#addFormOptions).

##### Add form options
`addFormOptions` __type__: `array`

> **Note** By default, the formOptions are automatically filled with sensible defaults. Only use these if you want to override specific options

With `addFormOptions` you can set/overwrite specific Symfony form options such as `multiple`, `query_builder` and all other available properties for the new/edit form. The exact available properties depend on the selected form type.

This example set the `required` option to `true` and add a custom `query_builder`:
```yaml
  addFormOptions:
    required: true
    query_builder: "function ($er) { return $er->createQueryBuilder('d')->orderBy('d.name'); }"
```

##### Credentials
`credentials` __type__: `string`

It is possible to set different credentials for every field, which should evaluate to `true` before an user is allowed to view the field. By default there are no credentials per field, but the credentials for the whole form are set on a higher level. See the [general credential configuration](credential-config) for more information.

##### Custom view
`customView` __type__: `string`

Used to set a custom view for a specific field. See the [custom view](custom-view) documentation at the general params.

##### Database format
`dbFormat` __type__: `string`

If set, formats field for scopes and filters. The formatting is a simple sprintf with one string argument (field name). As example, for field "createdAt" with `dbFormat: "DATE(%s)"` the output will be `DATE(createdAt)`

If undefined, the field will not be formatted. Since the functions may vary in different Database types, Admingenerator does not, by default, format fields in any way. It is up to developer to implement this for his fields.

> **Note**: this feature was created mainly for Date/DateTime fields.

##### Database Type
`dbType` __type__: `string`

Used to set the database type for a virtual field or unrecognized database type. It is possible to add columns to the list view/forms which are not a property of the object, but only methods. In this case, the generator needs to know it's database type, such that it can generate the correct field for the list view.

Secondly, it might be the case that you extended Doctrine/Propel with your own database type definition. Here you can set how the generator should handle the type. Note that it can also be configured globally in the [bundle configuration][configuration-db-types].


In this example, we set the admingenerator to handle an Image object as string in the database:
```yaml
image:
  label:            Organisation photo
  formType:         Admingenerator\FormExtensionsBundle\Form\Type\SingleUploadType
  dbType:           string
```

##### Extras
`extras` __type__: `string`

The extras parameter can be used to set 'extra' variables. It is not used by the admin generator, but you can use it for storing values which can be used in custom templates.

##### Filterable
`filterable` __type__: `bool` __default__: `false`

Set to `true` to make the field appear in the filters.

> **Note**: Only needed when the field guesser did not recognize it as a filterable field.

##### Filter on
`filterOn` __type__: `string`

Sets the property of an field (which is typically an object) on which it needs to be filtered. See the example at [sortOn](#sort-on).

##### Filter options
`filterOptions` __type__: `array`

> **Note** By default, the formOptions are automatically filled with sensible defaults. When using this parameter, all these autogenerated values are lost. If you want to overwrite or add an specific option, use [addFilterOptions](#add-filter-options).

The `filterOptions` are the Symfony form options that will be set for the filter form.

For examples, see [addFormOptions](#add-form-options).

##### Filter credentials
`filtersCredentials` __type__: `string`

If needed, you can provide credentials which are needed for the filters to show. Also see the [credentials](#credentials) parameter.

##### Filter type
`filterType` __type__: `string`

Same as [formType](#form-type), but then for the filter form.

##### Form options
`formOptions` __type__: `array`

> **Note** By default, the formOptions are automatically filled with sensible defaults. When using this parameter, all these autogenerated values are lost. If you want to overwrite or add an specific option, use [addFormOptions](#add-form-options).

The `formOptions` are the Symfony form options that will be set for the new/edit form.

For examples, see [addFormOptions](#add-form-options).

##### Form type
`formType` __type__: `string`

Can be used to set a specific form type to a field for whenever the autoguessed form type is not the wanted one. The example below sets the formtype to the a2lix_translations type:

```yaml
fields:
  translations:
    formType: A2lix\TranslationFormBundle\Form\Type\TranslationsType
```

##### Getter
`getter` __type__: `string`

Set the getter that needs to be used for this field. Usefull if you want to add a specific value which is not a database field to the list.

##### Grid class
`gridClass` __type__: `string`

By default, the generator uses the bootstrap `col-md-4` class for the fieldsets of the new/edit forms. You can set it to any bootstrap col class if wanted.

##### Help
`help` __type__: `string`

Set the field help message (not used by this bundle).

##### Label
`label` __type__: `string`

Set the field label.

##### Localized date format
`localizedDateFormat` __type__: `string`

Not used.

##### Localized time format
`localizedTimeFormat` __type__: `string`

Not used.

##### Many to many
`manyToMany` __type__: `string` __default__ `~`

If set, the entered value will be added to the filter columns.

##### Primary key
`primaryKey` __type__: `string`

If in any case the primary is not detected correctly, you can set the field name here.

##### Sortable
`sortable` __type__: `bool` __default__: `true`

By default, all fields in the List view are sortable. If you want to disable sorting on a specific property, set this to `false`.

##### Sort on
`sortOn` __type__: `string` __default__: `'default'`

When a sortable field is not easily sortable (such as an object), set the `sortOn` property to a field of that object.

In this example, the day property is a Day object which has an easy sortable field date:

```yaml
day:
  sortOn: day.date
```

##### Sort type
`sortType` __type__: `string`

The sort type depends on the database abstraction layer and will almost always be detected correctly. The supported sort types are `alphabetic`, `numeric` and `default`.


[back-to-index]: ../documentation.md
[form-types]: ../install/form_types/overview.md
[general-params]: general-params.md
[sf-form-doc]: http://symfony.com/doc/current/book/forms.html
[credential-config]: security.md
[custom-view]: general-params.md#custom-show-blocks
[configuration-db-types]: ../install/general-configuration.md#formfilter-types