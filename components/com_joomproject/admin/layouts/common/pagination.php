<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


extract($displayData);

?>

<?php if (($params->def('show_pagination', 1) == 1 || ($params->get('show_pagination') == 2)) && ($pagination->pagesTotal > 1)) : ?>
<div class="mt-4">
    <nav class="d-flex pagination pagination-wrapper">
		<?php if ($params->def('show_pagination_results', 1)) : ?>
            <div class="me-auto">
				<?php echo $pagination->getPagesLinks(); ?>
            </div>
            <div class="limit-box">
				<?php echo $pagination->getLimitBox(); ?>
            </div>
		<?php endif; ?>
    </nav>
    <div class="mt-1 text-muted">
		<?php echo $pagination->getPagesCounter(); ?>
    </div>
</div>
<?php endif; ?>
