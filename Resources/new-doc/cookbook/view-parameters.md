# Adding parameters in the view from the controller

[go back to Table of contents][back-to-index]

-----

Sometimes, you may need to transmit some parameters from your *generated* controller to your *generated* view.
You can simply do it by overriding the protected **getAdditionalRenderParameters** function into your bundle. Depending on controller *type*, prototype is different:

In the *list* controllers (`ListController` and `NestedListController`) the prototype is:
```php
protected function getAdditionalRenderParameters(); // No parameters
```

In the *edit*, *show* and *new* controllers (`EditController`, `ShowController` and `NewController`) the prototype is:
```php
protected function getAdditionalRenderParameters(\Full\Path\To\Your\Object $Object);
```

This function **must** return an array. By default, it returns an empty array.

> **Note:** this function is not available in `DeleteController` because it always returns a `RedirectResponse`.

[back-to-index]: ../documentation.md