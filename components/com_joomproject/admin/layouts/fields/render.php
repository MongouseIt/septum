<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

$JoomprojectParams 	= ComponentHelper::getParams('com_joomproject');
$layoutField = $JoomprojectParams->get('style_field_layout',0);

// Check if we have all the data
if (!key_exists('item', $displayData) || !key_exists('context', $displayData))
{
	return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item)
{
	return;
}

$context = $displayData['context'];

if (!$context)
{
	return;
}

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (key_exists('fields', $displayData))
{
	$fields = $displayData['fields'];
}
else
{
	$fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields))
{
	return;
}

$output = array();


   foreach ($fields as $field)
    {
        // If the value is empty do nothing
        if (!isset($field->value) || $field->value == '')
        {
            continue;
        }

        $class = $field->params->get('render_class');
        $layout = $field->params->get('layout', 'render');
        $content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));
        if($layoutField == 0){
            $output[] = '<dd class="field-entry ' . $class . '">' . $content . '</dd>';
        }elseif($layoutField == 1){
            $output[] = '<tr class="field-entry ' . $class . '">' . $content . '</tr>';
        }else{
            $output[] = '<li class="list-group-item d-flex justify-content-between align-items-center ' . $class . '">' . $content . '</li>';
        }
    }



if (empty($output))
{
	return;
}

?>
<?php if($layoutField < 2): ?>
<dl class="fields-container">
  <?php  if($layoutField == 0) : ?>
      <?php echo implode("\n", $output); ?>
    <?php  else : ?>
      <table class="table table-striped">
          <?php echo implode("\n", $output); ?>
      </table>
    <?php  endif; ?>
</dl>
<?php else:  ?>
    <div id="jpFieldsInColumn" class="fields-container" style="display: none">
        <?php echo implode("\n", $output); ?>
    </div>
<script>
    jQuery(document).ready(function($){
        $('#jpFieldsInColumn > li').appendTo('.card .article-info');
        $('#jpFieldsInColumn').show();
    })
</script>
<?php endif; ?>
