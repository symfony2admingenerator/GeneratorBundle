# AdminGenerator [![knpbundles.com](http://knpbundles.com/symfony2admingenerator/GeneratorBundle/badge-short)](http://knpbundles.com/symfony2admingenerator/GeneratorBundle) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/e8ee4e4c-d8fb-4354-96c3-8971dce11201/small.png)](https://insight.sensiolabs.com/projects/e8ee4e4c-d8fb-4354-96c3-8971dce11201)

[![Build Status](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/symfony2admingenerator/GeneratorBundle/?branch=master)

[![PHP Dependency Status](https://www.versioneye.com/user/projects/548f1209dd709d811f0001c3/badge.svg?style=flat)](https://www.versioneye.com/user/projects/548f1209dd709d811f0001c3)
[![JS Dependency Status](https://www.versioneye.com/user/projects/548f1202dd709d6dbd000118/badge.svg?style=flat)](https://www.versioneye.com/user/projects/548f1202dd709d6dbd000118) 

[![Latest Stable Version](https://poser.pugx.org/symfony2admingenerator/generator-bundle/v/stable.png)](https://packagist.org/packages/symfony2admingenerator/generator-bundle)
[![Total Downloads](https://poser.pugx.org/symfony2admingenerator/generator-bundle/downloads.png)](https://packagist.org/packages/symfony2admingenerator/generator-bundle)
[![License](https://poser.pugx.org/symfony2admingenerator/generator-bundle/license.png)](https://packagist.org/packages/symfony2admingenerator/generator-bundle)

## 1. Quick tour:

This bundle helps you quickly create powerful CRUD backend. Customizing the standard functionalities is simple - you can easily overwrite any part of the generated code. Most features can be configured in one (per model) YAML file. Advanced examples are covered in our [Cookbook][cookbook]. For a quick preview visit our [demo project][s2a-demo].

## 2. Highlights:

* one command to generate full CRUD for a model
* one configuration file (per model) to customize your backend
* supports popular model managers: **Doctrine ORM**, **Doctrine ODM** and **Propel**
* admin design based on [AdminLTE v2](https://github.com/almasaeed2010/AdminLTE)
* active community, support on [Gitter Chat][gitter-chat]

## 3. Documentation

The [documentation][documentation] for this bundle can be found in `Resources/doc` directory. 

## 4. Community and support

If you're having trouble or you found an error feel free to open a github ticket, but first please read [submitting issues][submitting-issues].
You can also find help on our chat. If you like this bundle join our SensioConnect club, follow us on Twitter and recommend us on KnpBundles.

[![Gitter Join Chat](http://img.shields.io/badge/Gitter-join%20chat-1dce73.svg)](https://gitter.im/symfony2admingenerator/GeneratorBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Follow @sf2admgen](http://img.shields.io/badge/Twitter-follow-55acee.svg)](https://twitter.com/intent/follow?screen_name=sf2admgen)
[![SensioConnect join the club](http://img.shields.io/badge/SensioConnect-join%20the%20club-82e83e.svg)](https://connect.sensiolabs.com/c/symfony2admingenerator/apply-membership)
[![KnpBundles recommend](https://img.shields.io/badge/KnpBundles-recommend-8DCAF8.svg)](http://knpbundles.com/symfony2admingenerator/GeneratorBundle/change-usage-status)

# 5. Versioning

Releases will be numbered with the format `major.minor.patch`.

And constructed with the following guidelines.

* Breaking backwards compatibility bumps the major.
* New additions without breaking backwards compatibility bumps the minor.
* Bug fixes and misc changes bump the patch.

For more information on SemVer, please visit [semver.org][semver] website.

> **Note:** while we're still in beta, these may not always apply.

When upgrading the to the newest build, always check our [upgrade notes][upgrade-notes].

# 5. Contributing

This bundle follows branching model described in [A successful git branching model][branching-model-post] blog post by Vincent Driessen.

* The `master` branch is used to tag stable releases.
* The `develop` branch is used to develop small changes and merge feature branches into it.
* The `feature-` branches are used to develop features. When ready, submit a PR to `develop` branch.
* The `hotfixes` branch is used to develop fixes to severe bugs in stable releases. When ready, the fix is merged both to `develop` and `master` branches.
* The release branches (eg. `1.1`) are created for each minor release and only patches will be merged into them.

![Branching model](https://github.com/symfony2admingenerator/FormBundle/raw/master/Resources/doc/img/branching-model.png)

# 6. License

This bundle is released under the [MIT License](LICENSE) except for the file: `Resources/doc/img/branching-model.png` by Vincent Driessen, which is released under `Creative Commons BY-SA`.

[cookbook]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/cookbook.md
[documentation]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/documentation.md
[submitting-issues]: https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/Resources/doc/support-and-contribution/submitting-issues.md
[gitter-chat]: https://gitter.im/symfony2admingenerator/GeneratorBundle
[s2a-demo]: https://github.com/symfony2admingenerator/symfony2-admingenerator-demo-edition
[semver]: http://semver.org
[branching-model-post]: http://nvie.com/posts/a-successful-git-branching-model/
[upgrade-notes]:  https://github.com/symfony2admingenerator/GeneratorBundle/blob/master/UPGRADE.md