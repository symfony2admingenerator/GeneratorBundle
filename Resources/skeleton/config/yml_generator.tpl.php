generator: <?= $generator ?><?= "\n" ?>
params:
    model: <?= $namespace ?>\<?= $modelFolder ?>\<?= $modelName ?><?= "\n" ?>
    namespace_prefix: <?= $namespacePrefix ?><?= "\n" ?>
    concurrency_lock: ~
    credentials: ~
    bundle_name: <?= $bundleName ?><?= "\n" ?>
    pk_requirement: ~
    fields: ~
    object_actions:
        delete: ~
    batch_actions:
        delete: ~
builders:
    list:
        params:
            title: List for <?= $bundleName ?><?= "\n" ?>
            display: ~
            filters: ~
            filtersMode: ~
            sort: ~
            max_per_page: ~
            fields: ~
            actions:
                new: ~
            object_actions:
                edit: ~
                delete: ~
    excel:
        params: ~
        filename: ~
        filetype: ~
    new:
        params:
            title: New object for <?= $bundleName ?><?= "\n" ?>
            display: ~
            actions:
                save: ~
                list: ~
    edit:
        params:
            title: "You're editing the object \"%object%\"|{ %object%: <?= $modelName ?>.title }|"
            display: ~
            actions:
                save: ~
                list: ~
    show:
        params:
            title: "You're viewing the object \"%object%\"|{ %object%: <?= $modelName ?>.title }|"
            display: ~
            actions:
                list: ~
                new: ~
    actions:
        params:
            object_actions:
                delete: ~
            batch_actions:
                delete: ~