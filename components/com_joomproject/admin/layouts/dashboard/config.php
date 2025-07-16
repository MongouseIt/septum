<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Layout\LayoutHelper;
$links = [
	[
		'icon' => 'fas fa-star',
		'title' => 'COM_JOOMPROJECT_CONFIG_GENERAL_LABEL',
		'url' => 'index.php?option=com_config&view=component&component=com_joomproject#general'
	],
	[
		'icon' => 'fas fa-plug',
		'title' => 'COM_JOOMPROJECT_CONFIG_COMPAT_LABEL',
		'url' => 'index.php?option=com_config&view=component&component=com_joomproject#compat'
	],
	[
		'icon' => 'fas fa-object-ungroup',
		'title' => 'COM_JOOMPROJECT_CONFIG_DISPLAY_LABEL',
		'url' => 'index.php?option=com_config&view=component&component=com_joomproject#display'
	]
];

?>
<div class="row gx-2">
	<?php foreach ($links as $link): ?>
		<?php echo LayoutHelper::render(
			'dashboard.skeleton.buttonLink',
			[
				'title' => $link['title'],
				'icon'  => $link['icon'],
				'url'   => $link['url']
			]
		) ?>
	<?php endforeach; ?>
</div>
