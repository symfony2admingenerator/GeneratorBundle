# List filters
---------------------------------------

[go back to Table of contents][back-to-index]

[back-to-index]: https://github.com/symfony2admingenerator/AdmingeneratorGeneratorBundle/blob/master/Resources/doc/builders/list

## Filters position

By default, the current template provides two ways to display filters: 
 * on top of results list
 * on the right of the results
 * in pop-up modal dialog
 
You can configure this position per generator into your `generator.yml` file under the list builder with the specific 
keyword `filtersMode`. Three values are currently interpreted:
 * `top`: filters will be on top of results
 * `default`: (default value), filters will be on the right of the results list
 * `modal`: filters will be displayed in modal dialog

Also in modal dialog mode (`filtersMode: modal`) you can setup desirable modal dialog size with the specific 
keyword `filtersModalSize`. Change the size of the modal by specifing one of these values:
 * `medium` (default)
 * `large`
 * `small`
 
### Default filters position (`filtersMode: default`)

![Filters default position preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-default-position-preview.png)

### Top filters position (`filtersMode: top`)

![Filters top position preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-top-position-preview.png)

![Filters collapsed top position preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-collapsed-top-position-preview.png)

### Filters in modal dialog mode (`filtersMode: modal`)

![Modal dialog list preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-modal-list-preview.png)

![Filters modal dialog medium preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-modal-medium-preview.png)

![Filters modal dialog large preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-modal-large-preview.png)

![Filters modal dialog small preview](https://github.com/symfony2admingenerator/GeneratorBundle/raw/master/Resources/preview/list-filters-modal-small-preview.png)
