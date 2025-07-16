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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
JLoader::register('JoomprojectHelperFrontend', JPATH_ROOT . '/components/com_joomproject/helpers/joomproject.php');

$function = Factory::getApplication()->input->getCmd('function', 'jpSelectAttachment');


$selectedItems = json_decode(Factory::getApplication()->input->get('selectedItems','','string'));

foreach ($this->items['files'] as $i => $item) :

	$icon = JoomprojectHelperFrontend::extToIcon($this->escape(strtolower($item->file_extension)));

	$js = '
	
	if (window.parent) window.parent.'
		. $this->escape($function)
		. '(\'' . $item->id . '\', \''
		. $this->escape(addslashes($item->title))
		. '\', \'file\''
		. ');';
	?>
    <tr style="<?php echo (is_array($selectedItems) && in_array('file.'.$item->id,$selectedItems)) ? 'display:none' : ''  ?>" data-repo-item-id="<?php echo $item->id ?>" class="row<?php echo $i % 2; ?>">
        <td>
            <small><a
                        data-repo-item-id="<?php echo $item->id ?>"
                        class="add-repo-item btn btn-success btn-sm text-white rounded-circle"
                        onclick="<?php echo $js; ?>"
                >
                    <i class="fas fa-plus"></i>
                </a></small>
        </td>
        <td>
            <i class="text-muted far fa-<?php echo $icon; ?>"></i>&nbsp;
			<?php echo $this->escape($item->title); ?>
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
