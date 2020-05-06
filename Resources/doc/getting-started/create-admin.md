    # Create an admin generator

[go back to Table of contents][back-to-index]

-----

To create an admin, simply execute the following command:
```
php app/console admin:generate-admin
```

It will ask a few simple questions to be able to configure the new admin correctly. Below are the questions with possible answers, we use the Acme/AdminBundle with an Article model:
 1. __Fully qualified bundle name__: Acme/AdminBundle
   * Use this field to indicate the target bundle. Make sure the name ends on Bundle and that you use a forward slash for the namespace. If the bundle does not exist, it will be created.
 2. __Target directory__: (use default)
   * If you have another location to generate/look for the new bundle, give it now.
 3. __Generator__: doctrine/doctrine_odm/propel
   * Choose the correct model manager for your model.
 4. __Model name__: Article
   * The complete name of the model you want to manage. Note that you want to give the fully qualified name if the model is not located in the same bundle.
 5. __Prefix of yaml__: AdminArticle
   * This text will be added as prefix to the generator config file for easy recognition.


#### Command options
If you do not want to use the step-by-step command, you can also directly use the parameters.

```
--namespace=NAMESPACE      The namespace of the admin to create
--dir=DIR                  The directory where to create the admin
--generator=GENERATOR      The generator service (propel, doctrine, doctrine_odm) [default: "doctrine"]
--model-name=MODEL-NAME    Base model name for admin module, without namespace. [default: "YourModel"]
--prefix=PREFIX            The generator prefix ([prefix]-generator.yml)
```

[back-to-index]: ../documentation.md