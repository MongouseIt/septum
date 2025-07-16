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
HTMLHelper::_('bootstrap.collapse');

HTMLHelper::_('jphtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = Factory::getApplication()->getIdentity();
$uid        = $user->get('id');

$project = (int) $this->state->get('filter.project');
$topic   = (int) $this->state->get('filter.topic');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
//$topic_in   = ($this->pagination->get('pages.current') == 1 ? 'in ' : '');
//$details_in = ($this->pagination->get('pages.current') == 1 ? ' active' : '');

$return_page     = base64_encode(JPforumHelperRoute::getRepliesRoute($topic, $project));
$link_edit_topic = JPforumHelperRoute::getRepliesRoute($topic, $project) . '&task=topicform.edit&id=' . $this->topic->id . '&return=' . $return_page;

$currentEditor = Factory::getConfig()->get('editor','none');
$editorName = ($currentEditor == 'jce') ? 'tinymce' : $currentEditor; // force to load tinymce instead of JCE cuz has problems
$editor          = \Joomla\CMS\Editor\Editor::getInstance($editorName);

$can_edit_topic     = $user->authorise('core.edit', 'com_jpforum.topic.' . $this->topic->id);
$can_edit_own_topic = ($user->authorise('core.edit.own', 'com_jpforum.topic.' . $this->topic->id) && $uid == $this->topic->created_by);

$doc   = Factory::getDocument();
$style = '.row-replies .well,.row-replies .btn-toolbar {'
        . 'margin-bottom: 0;'
        . '}'
        . '.img-avatar {'
        . 'max-height: 50px;'
        . 'max-width: 50px;'
        . 'margin-right: 10px;'
        . '}'
        . '.well-item {'
        . 'margin-left: 60px;'
        . '}';
$doc->addStyleDeclaration( $style );
?>
<script type="text/javascript">
/*Joomla.submitbutton = function(task)
{
	if (task == 'replyform.quicksave') {
		<?php // echo $editor->save('jform_description'); ?>
		Joomla.submitform(task);
	}
    else {
        Joomla.submitform(task);
    }
}
*/
</script>
<div id="joomproject" class="category-list<?php echo $this->pageclass_sfx;?> view-replies">

	<?php
	// load internal navigation
	echo JPhtmlNav::loadMain();
	?>

	<?php
	// load project internal navigation
	echo JPhtmlNav::loadProject();
	?>

    <h1><?php echo $this->escape($this->topic->title);?></h1>
    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo Route::_(JPforumHelperRoute::getRepliesRoute($topic, $project)); ?>" method="post" autocomplete="off">
	            <div class="mb-4">
                    <?php echo $this->toolbar; ?>
	            </div>


	            <div class="collapse mb-4" id="filters">
	                <div class="input-group mb-4">
	                        <input type="text" class="form-control" name="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                            <button type="submit" class="btn btn-secondary" data-bs-toggle="tooltip" data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-placement="top" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="input-group">
                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                            <select name="filter_published" class="form-control" onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                    <?php endif; ?>

                    <?php if (is_numeric($this->state->get('filter.project'))) : ?>
                            <select id="filter_author" name="filter_author" class="form-control" onchange="this.form.submit()">
                                <option value=""><?php echo Text::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo HTMLHelper::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                    <?php endif; ?>

                        <div class="input-group my-4">
                            <select name="filter_order" class="form-control" onchange="this.form.submit()">
			                    <?php echo HTMLHelper::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                            </select>
                            <select name="filter_order_Dir" class="form-control" onchange="this.form.submit()">
			                    <?php echo HTMLHelper::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                            </select>
                        </div>

	            </div>
	        </div>
	        <div class="-row-striped row-replies">
            <!-- Begin Topic -->

    			<div class="card mb-4">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-1">
                                        <img title="<?php echo $this->escape($this->topic->author_name);?>"
                                             src="<?php echo HTMLHelper::_('joomproject.avatar.path', $this->topic->created_by);?>"
                                             class="rounded-circle img-avatar float-start"
                                             data-bs-toggle="tooltip" data-placement="top"
                                        />
                                    </div>
                                    <div class="col-11">
                                        <h5 class="mb-2"><?php echo $this->escape($this->topic->author_name);?></h5>
                                        <div class="jp-description">
                                            <?php echo $this->topic->description; ?>
                                            <?php if (count($this->topic->attachment)) : ?>
                                                <fieldset>
                                                    <legend class="text-muted" style="font-weight: bold;"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?></legend>
                                                    <?php echo HTMLHelper::_('jprepo.attachments', $this->topic->attachment); ?>
                                                </fieldset>
                                            <?php endif; ?>
                                            <?php if (isset($this->topic->jcfields) && count($this->topic->jcfields) > 0 ) :?>
                                            <div class="customfields mt-4">
                                            <?php foreach ($this->topic->jcfields as $field) : ?>
                                                <?php
                                                if(!empty($field->value)){
                                                    echo ' <div class="mt-2">'.$field->label . ':' . $field->value.'</div>';
                                                }
                                                ?>
                                            <?php endforeach ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>




                                        <?php if ($can_edit_topic || $can_edit_own_topic) : ?>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted d-flex align-items-center"><?php echo HTMLHelper::_('date', $this->topic->created, $this->params->get('date_format', Text::_('DATE_FORMAT_LC2'))); ?></span>
                                                <div class="btn-group">
                                                    <a class="btn btn-warning btn-sm" href="<?php echo Route::_('index.php?option=com_jpforum&task=topicform.edit&id=' . $this->topic->id);?>">
                                                        <span aria-hidden="true" class="fas fa-edit"></span> <?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                </div>
			<!-- End Topic -->

            <!-- Start Replies -->

                    <?php if(count($this->items)> 0 ) : ?>

                <ul class="list-unstyled">
            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = JPforumHelper::getReplyActions($item->id);

                $can_create   = $access->get('core.create');
                $can_edit     = $access->get('core.edit');
                $can_change   = $access->get('core.edit.state');
                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

                $date_opts = array('past-class' => '', 'past-icon' => 'calendar');
            ?>

                <li class="media py-4 border-bottom">
                    <div class="d-flex w-100 justify-content-start">
                        <img title="<?php echo $this->escape($item->author_name);?>"
                             src="<?php echo HTMLHelper::_('joomproject.avatar.path', $item->created_by);?>"
                             class="rounded-circle me-3 img-avatar"
                             data-bs-toggle="tooltip" data-placement="top"
                        />
                        <div class="media-body">
                            <h4 class="mt-0 mb-3">
                                <?php echo $this->escape($item->author_name);?>
                                <?php if ($can_change || $uid) : ?>
                                    <label for="cb<?php echo $i; ?>" class="checkbox float-start">
                                        <?php echo HTMLHelper::_('jp.html.id', $i, $item->id); ?>
                                    </label>
                                <?php endif; ?>
                            </h4>

                            <?php echo $item->description;?>
                            <?php if (count($item->attachment)) : ?>
                                <fieldset>
                                    <legend class="small" style="font-weight: bold;"><?php echo Text::_('COM_JOOMPROJECT_FIELDSET_ATTACHMENTS'); ?></legend>
                                    <?php echo HTMLHelper::_('jprepo.attachments', $item->attachment); ?>
                                </fieldset>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between">
                                <?php if ($can_edit || $can_edit_own) : ?>
                                    <div class="d-flex align-items-center text-muted"><?php echo HTMLHelper::_('date', $item->created, $this->params->get('date_format', Text::_('DATE_FORMAT_LC2'))); ?></div>
                                    <div class="">
                                        <a class="btn btn-warning text-white btn-mini btn-sm" href="<?php echo Route::_('index.php?option=com_jpforum&task=replyform.edit&id=' . $item->id);?>">
                                            <span aria-hidden="true" class="fas fa-edit"></span> <?php echo Text::_('COM_JOOMPROJECT_ACTION_EDIT'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                </li>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>
                </ul>

                    <?php endif; ?>

            </div>


            <?php if ($this->access->get('core.create')) : ?>

                <h3 class="mb-4"><?php echo Text::_('COM_JOOMPROJECT_QUICK_REPLY');?>
                    <a href="javascript:void(0)" class="button btn btn-sm btn-sm btn-primary" onclick="Joomla.submitbutton('replyform.quicksave');"><i class="fas fa-check"></i> <?php echo Text::_('COM_JOOMPROJECT_ACTION_SEND');?></a>
                </h3>
                <div class="topic-reply">
                    <?php echo $editor->display('jform[description]', '', '100%', '250', 0, 0, false, 'jform_description'); ?>
                    <div class="clearfix"> </div>
                    <input type="hidden" name="jform[project_id]" value="<?php echo $project;?>" />
                    <input type="hidden" name="jform[topic_id]" value="<?php echo $topic;?>" />
                </div>

            <?php endif; ?>



	        <?php echo LayoutHelper::render('common.pagination',['pagination' => $this->pagination,'params' => $this->params]) ?>



            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="filter_project" value="<?php echo $project;?>" />
            <input type="hidden" name="filter_topic" value="<?php echo $topic;?>" />
            <input type="hidden" name="task" value="" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
