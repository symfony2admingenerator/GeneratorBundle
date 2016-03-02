# Internationalization

[Go back to Table of contents][back-to-index]

-----

This chapter assumes you are familiar with the way Symfony2 deals with internationalization. If not, please read [translation chapter][i18n-1] first.

## General messages

Common translations like *"Add"*, *"Save"* or *"Are you sure?"* that are shared among all generated admin screens are put into Admingenerator translation domain.

Currently supported languages are:

* (de) German
* (el) Hellenic (Greek)
* (en) English **-default-**
* (es) Spanish
* (fa) Farsi (Persian)
* (fr) French
* (it) Italian
* (ja) Japanese
* (nl) Dutch
* (pl) Polish
* (pt) Portuguese
* (ro) Romanian
* (ru) Russian
* (sl) Slovenian
* (tr) Turkish
* (uk) Ukrainian

If your language is not listed above please [contribute][support-contributing]!

> **Note:** Translation files can be found in `Admingenerator/GeneratorBundle/Resources/translations/Admingenerator.xx.yml`.

## Bundle specific messages

As you develop you will most likely need to add some translations that are specific to your project. To translate those you need to specify `i18n_catalog`:

```yaml
generator: admingenerator.generator.xxxx
params:
  i18n_catalog: YourTranslationDomain
```

Symfony will look for translations in:
* `../Resources/translations/YourTranslationDomain.xx.yml`
* `../Resources/translations/YourTranslationDomain.xx.xliff`

> **Note:** If not set, the default domain will be "Admin".

## Translation parameters

This bundle allows translations with parameters. Use one of the following methods:

### Backwards compability format

Parameters are passed in Twig-like format.

*Example*: `You're editing {{ Book.title }} written by {{ Book.author.name }}!`

To translate them simply replace the twig tag opening and closing brackets with `%` (percent) signs.

```yaml
# AcmeBookstoreBundle.pl.yml
"You're editing %Book.title% written by %Book.author.name%!":    "Edytujesz %Book.title% autorstwa %Book.author.name%!"
```

> **Note:** This method was introduced to keep backwards-compability. We recommend you use *Keywords method with abbreviated syntax parameter bag*.

### Parameter bag

To pass parameters along with message you need to add parameter bag.

Admingenerator requires a very strict syntax in order to succesfully read parameters.

* *Parameter bag* is always appended to original message
* *Parameter bag* begins with `|{ ` and ends with ` }|` (spaces after opening 
and before closing brackets are important!)
* Parameters in *parameter bag* are seperated by `, ` (comma and space)
* Parameter keys and values can consist only of a-z, A-Z, 0-9 and . (dot) characters

### Full syntax parameter bag

The full syntax approach lets you name your parameters.

#### Message method with full syntax parameter bag

*Example*: `You're editing %book% written by %author%!|{ %book%: Book.title, %author%: Book.author.name }|`

```yaml
# AcmeBookstoreBundle.pl.yml
"You're editing %book% written by %author%!":    "Edytujesz %book% autorstwa %author%!"
```
#### Keyword method with full syntax parameter bag

*Example*: `book.edit.title|{ %book%: Book.title, %author%: Book.author.name }|`

```yaml
# AcmeBookstoreBundle.pl.yml
book.edit.title:    "Edytujesz %book% autorstwa %author%!"
```

### Abbreviated syntax parameter bag

Specifying custom keys for each parameter seemed redundant, so we introduced **abbreviated syntax** where parameter value is also it's key.

#### Message method with abbreviated syntax parameter bag

*Example*: `You're editing %Book.title% written by %Book.author.name%!|{ Book.title, Book.author.name }|`

```yaml
# AcmeBookstoreBundle.pl.yml
"You're editing %Book.title% written by %Book.author.name%!":    "Edytujesz %Book.title% autorstwa %Book.author.name%!"
```

#### Keyword method with abbreviated syntax parameter bag

*Example*: `book.edit.title|{ Book.title, Book.author.name }|`

```yaml
# AcmeBookstoreBundle.pl.yml
book.edit.title:    "Edytujesz %Book.title% autorstwa %Book.author.name%!"
```

> **Note:** We recommend you use the keyword method with abbreviated syntax parameter bag. This approach keeps `generator.yml` relatively clean and abstracts the generator from actual content.

## Example

```yaml
generator:            admingenerator.generator.doctrine
params:
  model:              Acme\DemoBundle\Entity\Baloon
  namespace_prefix:   Acme
  bundle_name:        DemoBundle
  i18n_catalog:       AcmeDemoBundle
  fields:
    id:
      label:  ID      # id label is not translated
    name:
      label:  baloon.name.label
    color:
      label:  baloon.color.label
      help:   baloon.color.help
    sandbags:
      formType:         collection
      extras:
        new_label:      baloon.sandbags.new.label
      addFormOptions:
        type:           \Acme\DemoBundle\Form\Type\Baloon\EmbedSandbagType
        allow_add:      true
        allow_delete:   true
        by_reference:   false
builders:
  list:
    params:
      title:        baloon.title.list
      display:      [ id, name, color ]
      actions:
        new: ~
      batch_actions:
        delete: 
          label:    baloon.delete.label
          confirm:  baloon.delete.confirm
        myCustomAction:
          label:    baloon.myCustomAction.label
          confirm:  baloon.myCustomAction.confirm
      object_actions:
        edit: ~
        delete: ~
      max_per_page: 12
  filters:
    params:
      display: ~
  new:
    params:
      title:        baloon.title.new
      display:      [ name, color, sandbags ]
      actions:
        list: ~
  edit:
    params:
      title:        baloon.title.edit|{ Baloon.name }| 
      display:      [ name, color, sandbags ]
      actions:
        list: ~
  delete: ~
```

[back-to-index]: ../documentation.md
[i18n-1]: http://symfony.com/doc/current/book/translation.html
[support-contributing]: support-and-contribution/contributing.md