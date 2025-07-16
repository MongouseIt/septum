<?php
/**
 * @package      Joomproject
 * @subpackage   Comments
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
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');

$state = $this->state;


$count = count($this->items) ;
$count = JPCommentsHelperComments::getCountOfPublishedComments($this->state->get('filter.item_id'),$this->state->get('filter.context'));

$tmpl = Factory::getApplication()->input->get('tmpl', '', 'word');
$context = Factory::getApplication()->input->get('context', '', 'string');


$addCommentUrl = '#comment-editor';



if ($tmpl != 'tmpl') {

    HTMLHelper::_('script', 'com_joomproject/joomproject/comments.js', array('version' => 'auto', 'relative' => true));

    $commentsInit = <<<js
jQuery(document).ready(function($){
        var comments = JPcomments.init();
    });
js;

    Factory::getDocument()->addScriptDeclaration($commentsInit);

    //$addCommentUrl = Uri::getInstance() . $addCommentUrl;


}


?>




<div id="joomproject" class="comments-list view-comments">
    <?php

    if ($tmpl != 'component'):
    // load internal navigation
    echo JPhtmlNav::loadMain();

    // load header
    echo JPhtmlNav::loadHeader($this->params);

    // load project internal navigation
    echo JPhtmlNav::loadProject();

    endif;

    ?>



    <form
            class="form-validate"
            id="commentForm"
            name="commentForm"
            method="post"
            action="<?php echo Route::_('index.php?option=com_jpcomments&view=comments'); ?>"
    >


        <div id="jp-comments-container">


                <div class="card mb-4">

                    <div class="card-header">
                        <div class="float-end">
                            <a class="btn btn-sm btn-light bg-light border" href="<?php echo $addCommentUrl ?>"><i
                                        class="fas fa-plus"></i> <?php echo Text::_('COM_JOOMPROJECT_WRITE_COMMENT') ?>
                            </a>
                        </div>
                        <h4 class="m-0">
                            <span id="comment_count"><?php echo $count; ?></span><?php echo ' ' . Text::_('COM_JOOMPROJECT_COMMENTS'); ?>
                        </h4>
                    </div>
                    <div class="w-100 p-3" id="jp-comments">
                        <?php echo $this->loadTemplate('items'); ?>
                    </div>


                </div>


            <?php
            if ($this->access->get('core.create')) :
                echo $this->loadTemplate('editor');
            endif;
            ?>


        </div>


        <input type="hidden" id="jform_context" name="jform[context]"
               value="<?php echo $this->escape($state->get('filter.context')); ?>"/>
        <input type="hidden" id="jform_item_id" name="jform[item_id]"
               value="<?php echo $this->escape($state->get('filter.item_id')); ?>"/>
        <input type="hidden" id="jform_project_id" name="jform[project_id]"
               value="<?php echo $this->escape($state->get('filter.project')); ?>"/>
        <input type="hidden" id="jform_id" name="jform[id]" value="0"/>
        <input type="hidden" id="jform_parent_id" name="jform[parent_id]" value="0"/>
        <input type="hidden" name="task" value="commentform.apply"/>
        <input type="hidden" name="format" value="json"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>

