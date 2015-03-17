# Custom Avatars
---------------------------------------

[go back to Table of contents][back-to-index]

[back-to-index]: https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle/blob/master/Resources/doc/documentation.md#7-cookbook

To customize the avatars simply create directories:

* `app/Resources/AdmingeneratorGeneratorBundle/views/Navbar`
* `app/Resources/AdmingeneratorGeneratorBundle/views/Sidebar`\

And create `avatar_source.html.twig` files in each directory.

Customize the template to render an URL to the correct image. Example implementation with `FormExtensionsBundle` configured to work with `VichUploaderBundle` and `LiipImagineBundle`:

```html+django
{# avatar_source.html.twig #}
{{ image_asset(app.user, 'avatar')|image_filter('avatar') }}
```

Once you're done, you **MUST** clear your cache (even in DEV mode) with `php bin/console cache:clear -e=prod` (or dev).
