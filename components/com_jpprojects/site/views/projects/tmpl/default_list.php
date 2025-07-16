<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpprojects
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2006-2016 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


HTMLHelper::_('jphtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));

$app    = Factory::getApplication();
$itemid = JPApplicationHelper::getActiveMenuItemId();

$list_url   = JPprojectsHelperRoute::getProjectsRoute($this->params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);

$print_url = $list_url . '&tmpl=component&layout=print';
$print_opt = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';


// items by cat grouping
$itemsGrouping = JoomprojectHelperFrontend::arrayGroupBy($this->items, 'category_title');

?>


    <?php
    // load header
    echo JPhtmlNav::loadHeader($this->params);
    ?>


    <div class="project-list">
        <form name="adminForm" id="adminForm" action="<?php echo Route::_($list_url); ?>" method="post">
            <div class="form-group mb-4">
				<?php echo $this->toolbar; ?>
                <a
                        class="btn btn-light btn-sm button"
                        id="print_btn"
                        href="javascript:void(0);"
                        onclick="window.open('<?php echo Route::_($print_url); ?>', 'print', '<?php echo $print_opt; ?>')"
                >
                    <i class="fas fa-print"></i> <?php echo Text::_('COM_JOOMPROJECT_PRINT'); ?>
                </a>

                <?php echo LayoutHelper::render(
                    'common.export',
                    [
                        'url' => JPprojectsHelperRoute::getProjectsRoute()
                            . '&tmpl=component'
                    ],
                    null,
                    ['client' => 'administrator', 'component' => 'com_joomproject']
                ) ?>

            </div>

            <?php echo LayoutHelper::render('projects.filter',['current' => $this]); ?>

			<?php foreach ($itemsGrouping as $cat => $itemGroup): ?>

                <div class="card mt-3">
					<?php if ($cat != $this->state->get('filter.category') && !is_numeric($this->state->get('filter.category'))) : ?>
                        <div class="card-header">
                            <h3>
								<?php echo $this->escape($cat); ?>
                            </h3>
                        </div>
					<?php endif; ?>
                    <ul class="list-group list-group-flush list-group-striped">
						<?php foreach ($itemGroup as $item): ?>
							<?php echo LayoutHelper::render(
								'project.listGroupItem',
								['item' => $item, 'current' => $this, 'params' => $this->params, 'state' => $this->state]
							); ?>
						<?php endforeach; ?>
                    </ul>
                </div>

			<?php endforeach; ?>


	        <?php echo LayoutHelper::render('common.pagination',['pagination' => $this->pagination,'params' => $this->params]) ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
