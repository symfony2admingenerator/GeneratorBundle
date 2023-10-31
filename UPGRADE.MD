# Upgrade notes

[go back to Table of contents][back-to-index]

-----

This page contains all upgrade notes since the release of the stable 2.0.0 version.

## Current `master` branch

- You need to load the `AdminGenerated` namespace manually in your frontend controller in order to have a valid cache when debug is enabled.
  Check the [base installation](Resources/doc/install/base-installation.md) on how to do that!

## 4.0.0

- Add support for static generated base builders, old behaviour is still the default
- Dropped PHPExcel support

## 3.0.0

- Symfony 5 support

## 2.5.0

- Added support for PHPSpreadsheet due to the abandonment of PHPExcel

## 2.4.0

- In order to keep form label translation behavior consistent, filter form labels are no longer translated. This can be achieved by performing this translation in the project, which was already needed for new and edit form labels.

## 2.3.0
- Form type service names will be generated based on form FQCN instead of model FQCN. Old naming is aliased to new service name to not have BC break.

## 2.2.0
- Fixed [#285](https://github.com/symfony2admingenerator/GeneratorBundle/issues/285): added cookbook entry regarding injection
  of additional services in form types. Documented method of registration of auto generated form types in service container.
- Fixed [#288](https://github.com/symfony2admingenerator/GeneratorBundle/issues/288): generated form types now have unique 
  service name. Old naming is aliased to new service name in order not to have BC break.

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
