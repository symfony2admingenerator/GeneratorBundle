# Show builder configuration

[go back to Table of contents][back-to-index]

-----

The show builder is the simplest builder of this bundle: it simply creates a page where the values of your object are displayed.

The show builder takes only a few parameters:
```yaml
show:
  title: ~
  display: ~
  actions: ~
```

#### Title
`title` __default__: `{basekey}.title` __type__: `string`

Used to set the title of the page. By default, it is filled with `{admingeneratorprefix}.title`.

#### Display
`display` __default__: `~` __type__: `array`

See the [edit/new documentation][edit-doc-display].

#### Actions
`actions` __default__: `~` __type__: `array`

Actions can be enabled by simply specifying them here with the value `~`. The following example will render the save and list action below the form:
```yaml
edit:
  params:
    actions:
	  list: ~
	  new: ~
```

You can also use self-defined action from your global generator parameters, or overwrite specific part of the actions. Check the [action documentation][action-doc] for more information.



[back-to-index]: ../documentation.md
[edit-doc-display]: builder-edit.md#display