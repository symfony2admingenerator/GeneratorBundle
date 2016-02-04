# Form customization

[go back to Table of contents][back-to-index]

-----

## Customize form options with PHP logic

If you need to customize your form options using some PHP logic, you have two options. The first option can be used when the option hold for every form, while the second option can be used for specific forms.

### Method 1: Options class

With the options class you can set options for fields in all generated forms. The class is located in the `Form/Type/{prefix}` directory of your admin generator bundle and is by default empty.

Within the class you can define methods for specific fields. If your field is `category`, the method should be called `getCategoryOptions`. The method will be called when creating the form and it gets the current field options and builder options as arguments. The functions return an array which is directly set as form options.

An example which configures a `query_builder` for the `category` field of the `Article` object (with generator prefix `AdminArticle`):

```php
<?php

namespace Acme\DemoBundle\Form\Type\AdminArticle;

/**
 * Options class
 */
class Options
{
  public function getCategoryOptions(array $fieldOptions, array $builderOptions = array()){
    $fieldOptions['query_builder'] = function (CategoryRepository $er) {
      return $er->getQueryBuilderForFind();
    };
    return $fieldOptions;
  }
}
```

When you need access to an authorization checker, simply add the `setAuthorizationChecker` method with the authorization checker as argument. The method will automatically be called before the options are retrieved, so for example:

```php
public function setAuthorizationChecker($authorizationChecker){
  $this->authorizationChecker = $autorizationChecker;
}
```

It can then be used in the `get{{field}}Options` method.

### Method 2: Form methods

When you want to overwrite a form option in a specific form (filter, new or edit), you can simply add a method to the form definition (located in `Form/Type/{prefix}` directory). The methods signature is the same as with the Options class

For example (using the same names aswith the Options class):

```php
<?php

namespace Acme\DemoBundle\Form\Type\AdminArticle;

use Admingenerated\AcmeDemoBundle\Form\BaseAdminArticleType\NewType as BaseNewType;

/**
 * NewType
 */
class NewType extends BaseNewType
{
  public function getCategoryOptions(array $fieldOptions, array $builderOptions = array()){
    $fieldOptions['query_builder'] = function (CategoryRepository $er) {
      return $er->getQueryBuilderForFind();
    };
    return $fieldOptions;
  }
}
```

[back-to-index]: ../documentation.md
