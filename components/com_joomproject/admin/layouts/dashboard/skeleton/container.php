<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Layout\LayoutHelper;
extract($displayData);

?>

<div class="row mb-3">
	<?php
	foreach ($counts as $countCard){
		echo LayoutHelper::render('dashboard.skeleton.countCard', $countCard);
	}
	?>
</div>

<div class="row">
	<div class="col-md-8">

		<?php
		foreach ($left as $widget){
			echo LayoutHelper::render('dashboard.skeleton.widgetCard', $widget);
		}
		?>

	</div>

	<div class="col-md-4">

		<?php
		foreach ($right as $widget){
			echo LayoutHelper::render('dashboard.skeleton.widgetCard', $widget);
		}
		?>

	</div>
</div>