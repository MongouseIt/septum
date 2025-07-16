<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
USE Joomla\CMS\Language\Text;
extract($displayData);
?>

<a
    class="btn btn-light btn-sm button"
    href="<?php echo $url ?>&layout=csv"
    title="<?php echo Text::_('COM_JOOMPROJECT_EXPORT_TO_CSV'); ?>">
    <i class="fas fa-file-csv"></i> <?php echo Text::_('COM_JOOMPROJECT_CSV'); ?>
</a>


