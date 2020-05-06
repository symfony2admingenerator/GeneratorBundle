<?= "<?php\n" ?>

namespace <?= $namespace ?>\Controller<?= $prefix ? sprintf('\\%s', $prefix) : '' ?>;

use Admingenerated\<?= $bundle ?>\Base<?= $prefix ?>Controller\<?= $action ?>Controller as Base<?= $action ?>Controller;

/**
 * <?= $action ?>Controller
 */
class <?= $action ?>Controller extends Base<?= $action ?>Controller
{
}
