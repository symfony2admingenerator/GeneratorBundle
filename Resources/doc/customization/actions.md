# Action customization

[go back to Table of contents][back-to-index]

-----

All actions are customizable, but you can also define custom actions that can be used in your admin. As described in 
the [admin actions documentation][admin-actions-docs], there are three types of actions:
* `batch_actions`
* `object_actions`
* `actions`

For every action type you can create custom actions. They all have the same options available.

> **Note:** excel, edit and show action names are reserved. The delete action is pre-configured.

### Action builders

When an action is defined with the `object_actions` or `batch_actions` which does not exists, a controller STUB will 
be generated. You will have to add code to the controller before your custom action will work.

There are two routes (and so methods) that handle all custom actions:
* `objectAction`: route for this action is `Vendor_BundleName_GeneratorPrefix_object`, for example: 
`Acme_DemoBundle_User_object`. It takes two arguments: `pk` (object primary key) and 
`action` (object action name).
* `batchAction` route for this action is `Vendor_BundleName_GeneratorPrefix_batch`, for example: 
`Acme_DemoBundle_User_batch`. This action requires *POST* method and two posted variables: 
`action` (batch action name) and `selected` (an array of selected object primary keys).

Additionally all batch actions are by default CSRF protected. Object actions can be CSRF protected, if you add 
`csrfProtected: true` in action config.

Depending on the value of `action` parameter, different method will be called. E.g. `/{pk}/delete` will call delete 
object action.


### Custom object action example

#### Object actions configuration

```yaml
# ...
params:
    # add "global" config shared between all builders
    # for object actions
    object_actions:
        impersonate:
            label:    Login as
            icon:     glyphicon-user
            route:    Homepage
            params:
                _switch_user:   "{{ User.username }}"
        lock:
            label:    Lock account
            icon:     glyphicon-lock
            route:    Acme_SecurityBundle_User_object # Optional
            params: # Optional
                pk:       "{{ User.id }}"
                action:   lock
            csrfProtected: true
            workflows:
                # if set, protects the action with a `workflow_can` check
                # see Symfony 3.2 workflow component
                - lock
            options:
                # this is the title for intermediate page
                # if JS is available then intermediate page will not be used
                title:  "Are you sure you want to lock this account?"

builders:
    list:
        params:
            # if action config is set to null (~)
            # then "global" shared config will be used
            object_actions:
                impersonate: ~
                lock: ~
                delete: ~
            # delete action is pre-configured, so it does not need a config

    actions:
        params:
            object_actions:
                lock: ~
            # we're generating a STUB for lock action
```

Let's have a quick look what's going on in this config. First of all, we're configuring `object_actions` under global 
`params`, so we don't have to configure them twice (for list and actions builder).

**Impersonate** action leads to `Homepage` route, which takes no parameters. Because of that `_switch_user` parameter 
will be appended as a GET parameter (e.g. `/?_switch_user=cedric`) which is exactly what we want. 
[Read more][impersonate-user] about impersonating a user in Symfony2 book, Security chapter.

**Lock** action is a custom action, for which we will generate a STUB and customize it in our ActionsController. So, 
first we configure `lock` action to use our `object_actions` route.

> **Note:** the route for object and batch actions uses `generator prefix`, not ~model name~!

You may have multiple generators for one model (e.g. for `User` model you may generate `BasicUser-generator.yml`, 
`AdvancedUser-generator.yml` and `AdminUser-generator.yml`). In such case remember that the route for BasicUser would 
be `Acme_SecurityBundle_BasicUser_object` or `Acme_SecurityBundle_BasicUser_batch` for batch actions.

This route requires two parameters: `pk`, in this case `{{ User.id }}` and `action` which is our action's name `lock`.

Next, we're setting `csrfProtected` to `true` to enable the built-in actions CSRF protection.

Last, we're configuring the intermediate page title. Intermediate page is only used if for some reason Javascript fails.

#### List builder

In list builder configuration we're adding actions we want to display. If action config is set to null (~) then global 
config will be used. Thanks to this little fix we can have different object actions in List and Actions builders, but 
share their configuration.

So, in list we want to display:

* `impersonate` -> custom object action leading to `Homepage` route
* `lock` -> custom object action using actions stub generator
* `delete` -> pre-configured default object action

#### Actions builder

In actions builder configuration we're adding actions we want to generate STUBs for. That is only `lock` action in 
this example.

#### Code your custom actions

Now, all we have to do is go to our bundle's directory and code what our `lock` action actually does. In our example we 
can do that by editing `Acme/SecurityBundle/Controller/UserController/ActionsController.php`.

```php

    /**
     * This function is for you to customize what action actually does
     */
    protected function executeObjectLock($User)
    {
        // In this example I use Doctrine ORM
        $em = $this->getDoctrine()->getManager();
        // Lock user
        $User->setLocked(true);
        // Save changes to database
        $em->persist($User);
        $em->flush();
    }

```

For each custom object action there are four methods generated:
* `attemptObject{{ ActionName }}` - handles common object actions behaviour like checking CSRF protection token or credentials
* `executeObject{{ ActionName }}` - holds action logic
* `successObject{{ ActionName }}` - called if action was successful
* `errorObject{{ ActionName }}` - called if action errored

> **Note:** The only method you **have to** overwrite is `executeObject{{ ActionName }}`.

### Custom batch action example

#### Batch actions configuration

```yaml
# ...
params:
    # add "global" config shared between all builders
    # for object actions
    batch_actions:
        lock:
            label:    Lock account
            icon:     glyphicon-lock
            route:    Acme_SecurityBundle_User_batch

builders:
    list:
        params:
            # if action config is set to null (~)
            # then "global" shared config will be used
            batch_actions:
                lock: ~
                delete: ~
            # delete action is pre-configured, so it does not need a config

    actions:
        params:
            batch_actions:
                lock: ~
            # we're generating a STUB for lock action
```

Let's have a quick look what's going on in this config. Similar to object actions configuration, first we're configuring 
"global" batch actions. Batch actions configuration is a bit shorter, because all batch actions are always CSRF protected, 
and there is no intermediate page, so we don't have to specify a title for it.

Also, all required parameters (action name, selected objects, csrf token) are rendered as part of form on List view, so 
they need no further configuration.

#### List builder

In list builder configuration we're adding actions we want to display. If action config is set to null (~) then global 
config will be used. Thanks to this little fix we can have different batch actions in List and Actions builders, but 
share their configuration.

So, in list we want to display:

* `lock` -> custom batch action using actions stub generator
* `delete` -> pre-configured default batch action

#### Actions builder

In actions builder configuration we're adding actions we want to generate STUBs for. That is only `lock` action in this 
example.


#### Code your custom actions

Now, all we have to do is go to our bundle's directory and code what our `lock` action actually does. In our example we 
can do that by editing `Acme/SecurityBundle/Controller/UserController/ActionsController.php`.

```php

    /**
     * This function is for you to customize what action actually does
     */
    protected function executeBatchLock($selected)
    {
        // In this example I use Doctrine ORM
        $em = $this->getDoctrine()->getManager();
        // Lock users
        $em->createQuery('UPDATE Acme\SecurityBundle\Entity\User u SET u.locked = :locked WHERE u.id IN (:selected)')
           ->setParameter('locked', true)
           ->setParameter('selected', $selected)
           ->getResult();
    }

```

Similarly to object actions, for each custom batch action there are four methods generated:

* `attemptBatch{{ ActionName }}` - handles common object actions behaviour like
checking CSRF protection token or credentials
* `executeBatch{{ ActionName }}` - holds action logic
* `successBatch{{ ActionName }}` - called if action was successful
* `errorBatch{{ ActionName }}` - called if action errored

> **Note:** The only method you **have to** overwrite is `executeBatch{{ ActionName }}`

### Valid action names

Because admingenerator generates functions based on action name, action names must be validated. Actions names cannot 
contain characters like `!@#$%^&*;:"',.()[]{}`, they may contain only word-characters and dashes.

Any non-word character will be removed from generated function name, e.g. object action `toggle-is-valid` will generate 
functions:

```php

protected function attemptObjectToggleisvalid() { ... }

protected function executeObjectToggleisvalid() { ... }

protected function successObjectToggleisvalid() { ... }

protected function errorObjectToggleisvalid() { ... }
```


### Action parameters

* [Class](#class)
* [Confirm](#confirm)
* [ConfirmModal](#confirmModal)
* [Credentials](#credentials)
* [CsrfProtected](#csrf-protected)
* [ForceIntermediate](#force-intermediate)
* [Icon](#icon)
* [Label](#label)
* [Options](#options)
* [Params](#params)
* [Route](#route)
* [Submit](#submit)

##### Class

`class` __type__: `string`

Add any css class(es) to the rendered button.

##### Confirm

`confirm` __type__: `string`

Used to set a confirm message. When set, the action will first use a javascript popup with your confirm message to ask 
for confirmation from the user.

##### ConfirmModal

`confirmModal` __type__: `string`

Used to set an id of modal confirm dialog. Use when you want to use different id than default:
* `confirmGenericModal`: Used for generic-actions
* `confirmBatchModal`: Used for batch-actions
* `confirmObjectModal`: Used for object-actions

This is not needed unless you want to use a dialog with special field(s). For example action with a parameter.

##### Credentials

`credentials` __type__: `string`

By default, there are no credentials required to show and use an action. To check for a specific credential, just enter 
it here. For more documenation about credentials, check our [security documentation][security-doc].

> __NOTE__ Credentials given here are valid for the whole admin, but can be overridden in specific builders or even 
specific fields.

##### Workflows

`workflows` __type__: `array`

This parameter is implemented only for **object actions** as Workflow Component transition checks can be only made given entity context. Empty by default, if set - the action will check for if given transitions can be made - and only then the button will be rendered and corresponding controller will allow to complete the action.

##### CSRF protected

`crsfProtected` __type__: `bool` __default__: `false`

When set to `true` an extra crsf token is added to the `data-crsf-token` of the button.

##### Force intermediate

`forceIntermediate` __type__: `array` __default__: `false`

When set to `true` the intermediate confirm page will always be used instead of the javascript confirm.

##### Icon

`icon` __type__: `string`

Set the icon that is used in the button. For example `fa-book`.

##### Label

`label` __type__: `string`

Set the label of the button.

##### Options

`options` __type__: `array`

Can be used to set specific settings for the actions. Currenlty inplemented:

* `title`: Used to set the page title of the intermediate page
* `success`: Used to set the success message in the flashbag on action success
* `error`: Used to set the error message in the flashbag on action error
* `i18n`: Used to set the translations catalogue

##### Params

`params` __type__: `array`

Set the params used for route generation.

##### Route

`route` __type__: `string`

Set the action of the button. When this is set, there will be no controller STUB, as it will not be used. The button is 
rendered as simple URL.

##### Submit
`submit` __type
__: `bool`

If set to true, the button will behave as a submit button for the form on that page.

[back-to-index]: ../documentation.md
[admin-actions-docs]: ../admin/actions.md
[impersonate-user]: http://symfony.com/doc/current/book/security.html#impersonating-a-user
