<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();


extract($displayData);

// no need to continue if not item found
if (!$item)
    return;


?>



<?php if ($item->parent_id > 0): // skip root and display with no children ?>
    <?php echo LayoutHelper::render('comment', ['item' => $item]) ?>
<?php endif; ?>

<?php if (count($item->childs) > 0): // children ?>

    <?php foreach (array_reverse($item->childs, true) as $i => $item) : ?>

        <?php if($item->level > 1): ?>
        <div class="border-left-dotted ms-5">
        <?php endif; ?>

                <?php echo LayoutHelper::render('comments', ['item' => $item, 'i' => $i]) ?>

        <?php if($item->level > 1): ?>
        </div>
        <?php endif; ?>

    <?php endforeach; ?>

<?php endif; ?>
