# Actions configuration

[go back to Table of contents][back-to-index]

-----

Actions are configurable actions which can be applied on multiple sets of data:

  * `batch_actions`, which are applied on a selection of objects from the (nested) list view.
  * `object_actions`, which are applied on a specific, already saved object.
  * `actions`, generic actions of which the result does not depend on a specific object and do not have a controller.

The configuration of the actions is done in the generator config yaml file, at which you can also define [custom actions][custom-actions].

> **Note**: All customizations as described in the [custom actions][custom-actions] documentation can also be applied to the predefined actions, overwriten their defaults.

If you want to adjust the list, excel, new, edit or show view, check the [builder configuration][builder-config] documentation.

> **Note**: Keep in mind that every piece of controller code can be overwritten in your bundle. Check the cache to find what is generated in your case.

### Batch actions

Batch actions are only available in the list view and are rendered at the bottom of the list (left half of the screen). There is a single pre-defined batch action: `delete`.

##### Delete batch actions

The delete action can be used on a batch of objects and is always available. It has been preconfigured, so you can simple show it by adding it to the corresponding builder (list):

```yaml
builders:
  list:
    params:
      batch_actions:
        delete: ~
```

> **Note**: Although the delete action might not be listed in your builder, it will always be available and executable by anyone who can guess the URL. Set a non-existing role as credential to prevent this, for example (when using JMSSecurityExtraBundle):
```yaml
delete:
  credentials: 'hasRole("NO_DELETE")'
```

### Object actions

Object actions are coupled to specific object. In the list view they are rendered as small buttons next to the object, in the show and edit view they are rendered at the bottom left. There are multiple predefined object actions:

* `delete`
* `edit`
* `show`

All actions can simply be enabled by adding them to the `object_actions` of the wanted builder:

```yaml
builders:
  list:
    params:
      object_actions:
        delete: ~
        edit: ~
        show: ~
```

##### Delete object action
The delete action can be used to delete the coupled object and is always available.

> **Note**: Although the delete action might not be listed in your builder, it will always be available and executable by anyone who can guess the URL. Set a non-existing role as credential to prevent this, for example (when using JMSSecurityExtraBundle).
```yaml
delete:
  credentials: 'hasRole("NO_DELETE")'
```

##### Edit object action

The edit object actions represents a simple edit link which opens the edit form of the coupled object. For custumization of the edit form, see the [builder configuration][builder-config-edit].

##### Show object action

The same as the edit object action, but now it represents the show link. For custumization of the show view, see the [builder configuration][builder-config-show].

### Generic actions

Generic actions are not coupled to a specific object and are always rendered at the bottom of the page, on the right. There are multiple predefined generic actions:
* `excel`
* `list`
* `new`
* `save`
* `save-and-list`
* `save-and-add`
* `save-and-show`

The actions can simple be enabled by adding them to the `actions` of the wanted builder:

```yaml
builders:
  list:
    params:
      actions:
        list: ~
        save: ~
        save-and-add: ~
        save-and-list: ~
```

> **Note**: The save actions are special, as they are defined as submit buttons. See the [custom actions][custom-actions].

> **Note**: General actions do not have a generated controller.

##### Excel generic action

Represents the excel url, which generates an Excel export. For custumization of the Excel export, see the [builder configuration][builder-config-excel].

##### List generic action

Represents the list url, which shows a list of all (filtered) objects. For custumization of the list view, see the [builder configuration][builder-config-list].

##### New generic action

Represents the new url, which is used to create a new object. For custumization of the new form, see the [builder configuration][builder-config-new].

##### Save generic action

Represents the save submit button at the bottom of a form view.

##### Save and list generic action

Represents a save button at the bottom of a form view, but when saving the object was successful, the user is forwarded to the list view.

##### Save and add generic action

Represents a save button at the bottom of a form view, but when saving the object was successful, the user is forwarded to the add view.

##### Save and show generic action

Represents a save button at the bottom of a form view, but when saving the object was successful, the user is forwarded to the show view of the object.

### Custom actions

Custom actions can be made in each scope (batch, object and general) and will need a configuration and possibly an action in the Action controller. For more information about action customization, see the [corresponding docs][custom-actions].

[back-to-index]: ../documentation.md
[custom-actions]: ../customization/actions.md
[builder-config]: builders.md
[builder-config-edit]: builders.md#edit-builder
[builder-config-excel]: builders.md#excel-builder
[builder-config-list]: builders.md#list-builder
[builder-config-new]: builders.md#new-builder
[builder-config-show]: builders.md#show-builder
