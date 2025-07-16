<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_jpforum
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('jphtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');


$repo_enabled = JPApplicationHelper::enabled('com_jprepo');

$doc   = Factory::getDocument();
$style = '.row-topics .well,.row-topics .btn-toolbar {'
	. 'margin-bottom: 0;'
	. '}'
	. '.list-comments img,.collapse-comments img {'
	. 'margin-right: 10px;'
	. '}'
	. '.img-avatar {'
	. 'max-height: 50px;'
	. 'max-width: 50px;'
	. 'margin-right: 10px;'
	. '}'
	. '.well-item {'
	. 'margin-left: 60px;'
	. '}'
	. '.collapse-comments blockquote {'
	. 'margin-left: 50px;'
	. '}'
	. '.collapse-comments .btn-toolbar {'
	. 'margin: 0 0 0 50px;'
	. '}';
$doc->addStyleDeclaration($style);
?>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx; ?> view-topics PrintArea all">

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

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPforumHelperRoute::getTopicsRoute()); ?>"
              method="post">
            <div class="mb-4">
				<?php echo $this->toolbar; ?>
                <div class="filter-project btn-group">
					<?php echo HTMLHelper::_('jphtml.project.filter'); ?>
                </div>
            </div>

            <?php echo LayoutHelper::render('topics.filter',['current' => $this]); ?>


            <div class=" row-discussions row-topics">
                <ul class="list-group">
					<?php
					$k = 0;
					foreach ($this->items as $i => $item) :

						$access = JPforumHelper::getActions($item->id);

						$can_edit     = $access->get('core.edit');
						$can_change   = $access->get('core.edit.state');
						$can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

						// Prepare the watch button
						$watch = '';

						if ($uid)
						{
							$options = array('div-class' => '', 'a-class' => 'btn-sm btn-mini');
							$watch   = HTMLHelper::_('jphtml.button.watch', 'topics', $i, $item->watching, $options);
						}
						?>
                        <!-- Begin Topic -->
                        <li class="media list-group-item">
                            <a href="<?php echo Route::_(JPforumHelperRoute::getTopicRoute($item->slug, $item->project_slug)); ?>">
                                <img title="<?php echo $this->escape($item->author_name); ?>"
                                     src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->created_by); ?>"
                                     class="rounded-circle img-avatar float-start me-3"
                                     data-bs-toggle="tooltip" data-placement="top"
                                />
                            </a>
                            <div class="media-body">
                                <span class="text-muted float-end"><?php echo HTMLHelper::_('date', $item->created, $this->params->get('date_format', Text::_('DATE_FORMAT_LC2'))); ?></span>
								<?php if ($can_change || $uid) : ?>
                                    <label for="cb<?php echo $i; ?>" class="checkbox float-start p-0">
										<?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
                                    </label>
								<?php endif; ?>
                                <h5 class="mt-0 mb-3">
                                    <a href="<?php echo Route::_(JPforumHelperRoute::getTopicRoute($item->slug, $item->project_slug)); ?>">
										<?php if ($item->checked_out) : ?><i class="fas fa-lock"></i> <?php endif; ?>
										<?php echo $this->escape($item->title); ?>
                                    </a>
                                </h5>
                                <div class="jp-description">
									<?php echo HTMLHelper::_('jp.html.truncate', $item->description, 300); ?>
                                </div>
                                <div class="mt-4">
									<?php echo $item->event->beforeDisplayContent; ?>
                                </div>


                                <div class="mt-3 d-flex justify-content-between align-items-center">

                                    <div>
                                        <?php if ($can_edit || $can_edit_own) : ?>
                                            <div class="btn-group">
                                                <a class="btn btn-sm btn-light"
                                                   href="<?php echo Route::_('index.php?option=com_jpforum&task=topicform.edit&id=' . $item->id); ?>">
                                                <span aria-hidden="true"
                                                      class="fas fa-edit"></span> <?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="btn-group">
                                            <a class="btn btn-light btn-sm"
                                               href="<?php echo Route::_(JPforumHelperRoute::getTopicRoute($item->slug, $item->project_slug)); ?>">
                                            <span aria-hidden="true"
                                                  class="fas fa-comment"></span> <?php echo Text::plural('COM_JOOMPROJECT_N_REPLIES', (int) $item->replies); ?>
                                            </a>
                                        </div>
                                        <?php echo $watch; ?>
                                    </div>


                                    <?php echo \Joomla\CMS\Layout\LayoutHelper::render('project.link',['item' => $item],'',['client' => 'site','component' =>  'com_jpprojects']) ?>


                                </div>



                            </div>
                        </li>

                        <!-- End Topic -->
					<?php
						//  $k = 1 - $k;
					endforeach;
					?>
                </ul>
            </div>

			<?php echo LayoutHelper::render('common.pagination', ['pagination' => $this->pagination, 'params' => $this->params]) ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value=""/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
