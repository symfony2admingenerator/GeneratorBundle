# Command Line
---------------------------------------

[go back to Table of contents][back-to-index]

[back-to-index]: https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle/blob/master/Resources/doc/documentation.md#7-cookbook

This bundle sole purpose is to speed up the process of generating a fully featured backend for your models.
To get you started, use one of the following commands, depending on whether your model already exists:

## 1. Generate admin in an existing bundle

If you have already [created][symfony-1] a bundle and added some models, use this command:

``` bash
$ php app/console admin:generate-admin
```

The interactive dialog will guide you step-by-step. Anwser all questions and confirm generation.

## 2. Generate a bundle and admin

To generate the whole bundle structure along with admin use command:

``` bash
$ php app/console admin:generate-bundle
```

The interactive dialog will guide you step-by-step. Anwser all questions and confirm generation.
