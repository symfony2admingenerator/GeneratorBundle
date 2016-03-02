# List builder configuration

[go back to Table of contents][back-to-index]

-----

The list builder is used to configured the list view. It can takes the following configuration:

```yaml
builders:
  list:
    params:
      title: ~
      filters: ~
      filtersMode: ~
	  filtersModalSize: ~
      fields: ~
      display: ~
      actions: ~
      object_actions: ~
      batch_actions: ~
```


### Title
`title` __default__: `{basekey}.title` __type__: `string`

Used to set the title of the page. By default, it is filled with `{admingeneratorprefix}.title`.


### Filters

`filters` __default__: `~` __type__: `array`
`filtersMode` __default__: `default` __type__: `string`
`filtersModalSize` __default__: `medium` __type__: `string`

By default, all filterable fields are available as filter. If you want to specify which filters (and in which order) 
you want to display, simply fill the `filters` array:

```yaml
list:
  params:
    filters: [ name, gender ]
```

If you want to adjust the rendering of the filter field, please refer to the 
[field configuration documentation][field-doc]. Several filter related configuration parameters are described there.

More information about the `filtersMode` and `filtersModalSize` can be found in the [filter documentation][filter-doc].


### Fields

`filters` __default__: `~` __type__: `array`
See the [field configuration documentation][field-doc].

> **Note**: The field and filter parameters have the same effect in the list view. However note that the filter 
parameters overwrite the form parameters, if any.

#### Display

`display` __default__: `~` __type__: `array`

With the display parameter you can specify the fields that need to be displayed. Works the same as the 
[`filters`](#filters) parameter.

### Actions

Actions can be enabled by simply specifying them here with the value `~`. The following example will render the new 
action on the right below the list, the batch delete action on the left below the list and the edit action for every 
object:

```yaml
edit:
  params:
    actions:
	  new: ~
	object_actions:
	  edit: ~
    batch_actions:
	  delete: ~
```

You can also use self-defined actions from your global generator parameters, or overwrite specific part of the actions. 
Check the [action documentation][action-doc] for more information.


[back-to-index]: ../documentation.md
[action-doc]: actions.md
[field-doc]: fields.md
[filter-doc]: filters.md