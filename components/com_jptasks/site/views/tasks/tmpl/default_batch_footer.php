<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<a 
    class="btn"
    type="button"
    onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''"
    data-dismiss="modal">
    <?php echo Text::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('taskform.batch');">
    <?php echo Text::_('COM_JOOMPROJECT_BATCH_BUTTON'); ?>
</button> 