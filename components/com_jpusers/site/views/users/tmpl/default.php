<?php
/**
 * @package      Joomproject
 * @subpackage   Users
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2012 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
HTMLHelper::script('com_joomproject/joomproject/jquery.PrintArea.js', array('version' => 'auto', 'relative' => true));

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');

$doc   = Factory::getDocument();
$style = '.text-large {'
	. 'font-size: 16px;'
	. 'display: block;'
	. '}'
	. '.row-fluid .thumbnails.thumbnails-users > li[class*="span"]:first-child,.thumbnails.thumbnails-users > li[class*="span"] {'
	. 'margin-left: 0.7em;'
	. 'margin-bottom: 0.7em;'
	. '}'
	. '.thumbnails-users .img-circle {'
	. 'margin: 5px auto;'
	. '}'
	. '.thumbnails-users .img-polaroid {'
	. 'height: 197px;'
	. '}';
$doc->addStyleDeclaration($style);
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
	jQuery("div#print_btn").click(function(){		
		var options = {mode:"popup"};
		jQuery(".PrintArea.all").printArea(options);
	});
});
');
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-users PrintArea all">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

	<?php
	// load header
	echo JPhtmlNav::loadHeader($this->params);
	?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

    <div class="clearfix"></div>

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>"
              method="post">
            <div class="mb-4 clearfix">
                <div class="filter-project btn-group float-end">
					<?php echo $this->toolbar; ?>
                </div>
                <div class="btn btn-info btn-sm button b1" id="print_btn"><i
                            class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?></div>

				<?php echo HTMLHelper::_('jphtml.project.filter'); ?>

            </div>

            <div class="collapse mb-4" id="filters">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" class="form-control" name="filter_search"
                               placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search"
                               value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
                        <select name="filter_order" class="form-select" onchange="this.form.submit()">
							<?php echo HTMLHelper::_('select.options', $this->sort_options, 'value', 'text', $list_order, true); ?>
                        </select>
                        <select name="filter_order_Dir" class="form-select" onchange="this.form.submit()">
							<?php echo HTMLHelper::_('select.options', $this->order_options, 'value', 'text', $list_dir, true); ?>
                        </select>
                            <button type="submit" class="btn btn-secondary" data-bs-toggle="tooltip"
                                    data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
                                        class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="tooltip"
                                    data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                                    onclick="document.getElementById('filter_search').value='';this.form.submit();"><i
                                        class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>

            <div class="row">
				<?php
				//  $k = 0;
				foreach ($this->items as $i => $item) :
					$asset_name = 'com_users&task=profile.edit&user_id=.' . $item->id;
					$slug = $item->id . ':' . OutputFilter::stringURLSafe($item->username);
					?>
                    <div class="col-md-2 mb-4">
                        <div class="thumbnails card thumbnails-users">
                            <a href="<?php echo JPusersHelperRoute::getUserRoute($slug); ?>">
                                <img title="<?php echo $this->escape($item->name); ?>"
                                     src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->id); ?>"
                                     class="ard-img-top w-100" data-bs-toggle="tooltip" data-placement="top"

                                />
                            </a>

                            <div class="card-body">
                                <h5 class="card-title"><a href="<?php echo JPusersHelperRoute::getUserRoute($slug); ?>">
									<?php echo $this->escape($item->name); ?></h5>

                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><?php echo $this->escape($item->username); ?></li>
                                <li class="list-group-item"><a
                                            href="mailto:<?php echo $this->escape($item->email); ?>"><?php echo $this->escape($item->email); ?></a>
                                </li>
                            </ul>

                        </div>
                    </div>

				<?php
					// $k = 1 - $k;
				endforeach;
				?>
            </div>

			<?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
