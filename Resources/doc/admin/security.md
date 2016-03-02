# Credentials for actions and fields

[go back to Table of contents][back-to-index]

-----

### 1. Description

Credentials allow you to protect actions and fields depending on your own logic. Credentials are based on Symfony 
Security Component.

### 2. Usage and configuration

You can easily protect any action or field using the parameter `credentials` in your `generator.yml`. Credentials should 
be a valid `expression` string that could be used in a `isGranted` call from the `AuthorizationChecker` service. You 
can so, for example, easily protect any action or field using roles expression:

```yaml
  object_actions:
    delete:
      credentials: 'ROLE_ADMIN'
  fields:
    myField:
      credentials: 'ROLE_USER'
```

Because credentials are using native security component, you can use all the power of `Voters` to create more 
sophisticated scenarios than simple ROLE checkers. For more information, take a look to 
[How to Use Voters to Check User Permissions](http://symfony.com/doc/current/cookbook/security/voters.html) from the 
Symfony documentation.

### 3. Using JMS Security Extra Bundle

If you are used to use JMS bundle or simply like the `Security function` capacities from this bundle, the Admingenerator 
is able to use `Expression`. You need to turn on this functionality thanks to the `use_jms_security` configuration 
parameter.

 > Of course, you also need to install the JMS bundle and activate it by yourself.

### 4. Credentials configuration reference

#### Securing a field

You can secure a field globaly through the global `fields` entry:

```yml
generator: admingenerator.generator.doctrine
params:
    model:              Admingenerator\DoctrineOrmDemoBundle\Entity\Post
    namespace_prefix:   Admingenerator
    i18n_catalog:       AdmingeneratorOrmDemoBundle
    concurrency_lock:   ~
    bundle_name:        DoctrineOrmDemoBundle
    pk_requirement: ~
    fields:
        category:
            filterable: 1
            filterOn: category.name
            credentials: ROLE_USER # category field will be visible only if the user has the role ROLE_USER in all screens, including filters
```

#### Securing an action

To secure `generic` action, add the `credentials` parameter to the builder definition:

```yml
generator: admingenerator.generator.doctrine
params:
    ...
builders:
    new:
        params:
            credentials: ROLE_USER
            title: post.title.new
```

To secure an `object` or `batch` action, use the global section:

```yml
generator: admingenerator.generator.doctrine
params:
    model:              Admingenerator\DoctrineOrmDemoBundle\Entity\Post
    namespace_prefix:   Admingenerator
    i18n_catalog:       AdmingeneratorOrmDemoBundle
    concurrency_lock:   ~
    bundle_name:        DoctrineOrmDemoBundle
    pk_requirement: ~
    object_action:
        delete:
            credentials: ROLE_USER
        myCustomAction:
            credentials: MY_CUSTOM_SECURITY_CHECK
    batch_action:
        delete:
            credentials: ROLE_USER
```

#### Model object used in `isGranted` calls

| Origin                   | Model object instance  | Description                                                                                |
|--------------------------|------------------------|--------------------------------------------------------------------------------------------|
| New controller           | null                   | No object is used                                                                          |
| Edit controller          | Model                  | Object currently edited (retrieved from the `getObject` call)                              |
| Object action controller | Model                  | Object currently edited (retrieved from the `getObject` call)                              |
| Object action button     | Model                  | Object related to the action                                                               |
| Batch action controller  | null                   | No object is used                                                                          |
| Batch action button      | null                   | No object is used                                                                          |
| Field in header list     | null                   | No object is used                                                                          |
| Field in lists           | Model                  | Object of the current row                                                                  |
| Filter form field        | null                   | No object is used                                                                          |
| New form field           | Model                  | Called in the `PRE_SET_DATA` listener. Object used is the one from the `$event->getData()` |
| Edit form field          | Model                  | Called in the `PRE_SET_DATA` listener. Object used is the one from the `$event->getData()` |
| Field in show template   | Model                  | Object currently viewed                                                                    |

[back-to-index]: ../documentation.md