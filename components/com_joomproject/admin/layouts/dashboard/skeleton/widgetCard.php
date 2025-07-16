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

<div class="card mb-3 shadow-sm">
    <?php if(isset($title) && !empty($title)): ?>
	<div class="card-header">
		<h3 class="m-0 font-weight-light">
			<i class="fas fa-<?php echo $icon ?> text-info me-2"></i> <?php echo Text::_($title) ?></h3>
	</div>
    <?php endif; ?>
	<div class="card-body">
		<?php echo $bodyContent ?>
	</div>
	<?php if(isset($footerContent) && !empty($footerContent)): ?>
		<div class="card-footer bg-white">
			<?php echo $footerContent ?>
		</div>
	<?php endif; ?>
</div>
