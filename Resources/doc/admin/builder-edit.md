# Edit/New builder configuration

[go back to Table of contents][back-to-index]

-----

The Edit/New builders are basically the same: the only difference between the two is if the object has been prefilled 
by the database. However, by having both configurable separate, it is possible to create different form for both actions
and custom code in different situation easily (sending different event, custom fields, ...).

> **Note**: This page will use the Edit builder as example. Everywhere where edit is used, new can also be used for the 
New builder.

## Parameters

The Edit builder configuration looks as follows:

```yaml
builders:
  edit:
    params:
      title: ~
      display: ~
      actions: ~
	  fields: ~
```

#### Title

`title` __default__: `{basekey}.title` __type__: `string`

Used to set the title of the page. By default, it is filled with `{admingeneratorprefix}.title`.

#### Display

`display` __default__: `~` __type__: `array`

With the display parameter you can specify the fields that need to be displayed. This is done using groups (which are 
equivalent to the HTML fieldset). You can set a title for such group, or, if you do not want to use the groups, you 
can use `NONE`. If you want to use multiple groups without title, prefix them with the keyword `NONE_`.

> **Note**: When not specifing the fields to display (`~`), all fields will be displayed.

For example, a single group without fieldset element:

```yaml
edit:
  params:
    title: "You're editing the contact \"%object%\"|{ %object%: Contact.name }|"
    display:
      NONE:
        "row1 col-md-12": [ name, gender, address ]
```

> **Note** the `row1 col-md-12` in the example above. The name of the row will be used as class for the rows, and needs 
to be unique per builder. If not, they rows will overwrite each other, effectively resulting in only the last to be 
rendered.

If you want to use multiple fieldset with titles, consider the following example:

```yaml
edit:
  params:
    title: "You're editing the contact \"%object%\"|{ %object%: Contact.name }|"
    display:
      "Name information":
        "row1 col-md-12": [ name ]
      "Gender information":
        "row2 col-md-12": [ gender ]
      "Address information":
        "row3 col-md-12": [ address ]
```
> **Note** the different row numbers, to ensure they are unique.

If you do not care about the class of the rows and you do not want to use any groups, you can simply use an array. 
An example:

```yaml
edit:
  params:
    title: "You're editing the contact \"%object%\"|{ %object%: Contact.name }|"
    display: [ name, gender, address ]
```

#### Actions

`actions` __default__: `~` __type__: `array`

Actions can be enabled by simply specifying them here with the value `~`. The following example will render the save 
and list action below the form:

```yaml
edit:
  params:
    actions:
	  save: ~
	  list: ~
```

You can also use self-defined actions from your global generator parameters, or overwrite specific part of the actions. 
Check the [action documentation][action-doc] for more information.


#### Fields

`fields` __default__: `~` __type__: `array`

Fields configuration can be overwritten for this specific builder. For more information about the field configuration, 
take a look at it's [own documentation][field-doc].


[back-to-index]: ../documentation.md
[action-doc]: actions.md
[field-doc]: fields.md