<?= "<?php\n" ?>

namespace <?= $namespace ?>\Form\Type<?= $prefix ? sprintf('\\%s', $prefix) : '' ?>;

use Admingenerated\<?= $bundle ?>\Form\Base<?= $prefix ?>Type\<?= $form ?>Type as Base<?= $form ?>Type;

/**
 * <?= $form ?>Type
 */
class <?= $form ?>Type extends Base<?= $form ?>Type
{
}
