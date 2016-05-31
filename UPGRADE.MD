# Upgrade notes

[go back to Table of contents][back-to-index]

-----

This page contains all upgrade notes since the release of the stable 2.0.0 version.

## Current `master` branch

- Fixed [#288](https://github.com/symfony2admingenerator/GeneratorBundle/issues/288): 
Generated form types now have unique service name. Old naming is aliased to new 
service name in order not to have BC break.

## 2.1.0

- Fixed [#245](https://github.com/symfony2admingenerator/GeneratorBundle/issues/245): KnpMenuBundle usage is now optional. 
  To keep using the original functionality, follow the [KnpMenuBundle cookbook](Resources/doc/cookbook/knp-menu.md).
- Feature [#270](https://github.com/symfony2admingenerator/GeneratorBundle/pull/270): Forward on missing object during deletion. 
  When a object is not found, previously an InvalidArgumentException
  was thrown. This has changed to an HttpNotFoundException. By default, this exception is now catched to forward the 
  user to the list with the notification that the object no longer exists.
- Updated PagerFantaBundle. Required is now version <=1.0.4 or >=1.0.6. 
  1.0.5 breaks paginator behavior, but there should be no complications.

[back-to-index]: README.md