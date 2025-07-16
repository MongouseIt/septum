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
use Joomla\CMS\Layout\LayoutHelper;


$user     = Factory::getApplication()->getIdentity();
$ul_open  = false;
$level    = 1;
$uid      = $user->get('id');


$rootComment = JPCommentsHelperComments::restructureComments($this->items);

?>

<?php echo LayoutHelper::render('comments',['item' => $rootComment],'',['client' => 'site','option'=> 'com_jpcomments']) ?>