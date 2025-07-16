<?php
/**
 * @package      Joomproject
 * @subpackage   Repository
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
$function    = Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');
$user        = Factory::getApplication()->getIdentity();
$uid         = $user->get('id');
$this_dir    = $this->items['directory'];
$link_append = '&layout=modal&tmpl=component&function=' . $function;

$selectedItems = json_decode(Factory::getApplication()->input->get('selectedItems','','string'));


if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td></td>
        <td colspan="2">
            <a  class="btn btn-light btn-sm bg-light text-dark"  href="<?php echo Route::_(JPrepoHelperRoute::getRepositoryRoute($this_dir->project_id, $this_dir->parent_id, $this_dir->path) . $link_append);?>">
                <span aria-hidden="true" class="fas fa-arrow-left"></span> <?php echo Text::_('JPREVIOUS'); ?>
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $link   = JPrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path);
    $icon   = ($item->protected == '1' ? 'fas fa-exclamation-triangle' : 'fas fa-folder');

    if ($item->parent_id == '1') {
        $icon = 'fa-folder';
    }

    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'directory\''
        . ');';

    ?>

    <tr style="<?php echo (is_array($selectedItems) && in_array('directory.'.$item->id,$selectedItems)) ? 'display:none' : ''  ?>" data-repo-item-id="<?php echo $item->id ?>" class="row<?php echo $i % 2; ?>">
        <td>
            <small><a  data-repo-item-id="<?php echo $item->id ?>"  class="add-repo-item btn btn-success btn-sm text-white rounded-circle" onclick="<?php echo $js; ?>">
                    <i class="fas fa-plus"></i>
                </a></small>
        </td>
        <td>
            <i class="text-muted far fa-<?php echo $icon; ?>"></i>&nbsp;
            <a href="<?php echo Route::_($link . $link_append);?>">
		        <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td>
	        <?php if (!empty($item->description)): ?>
                <p class="m-0"><?php echo HTMLHelper::_('jp.html.truncate', $item->description); ?>&nbsp;</p>
	        <?php endif; ?>
            <p class="m-0 text-muted">
                <small>
                    <i class="far fa-user"></i> <?php echo $this->escape($item->author_name); ?>
                </small>
            </p>
        </td>
    </tr>
<?php endforeach; ?>
