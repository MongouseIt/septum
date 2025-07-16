<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

extract($displayData);

?>

<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
	<a
		title="<?php echo Text::_($title) ?>"
		class="btn d-block btn-light btn-sm border"
		<?php echo !empty($target) ? "target='$target'" : '' ?>
		href="<?php echo $url ?>"
	>
		<div class="mb-1"><i class="<?php echo $icon ?> text-muted"></i></div>
		<div class="text-truncate"><?php echo Text::_($title) ?></div>
	</a>
</div>
