<?php
/**
 * @package        JoomProject
 * @copyright      2013-2020 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\LayoutHelper;

$links = [
	[
		'icon' => 'fas fa-pager',
		'title' => 'COM_JOOMPROJECT_PRODUCT_PAGE',
		'url' => 'https://www.joomboost.com/joomla-components/138-joomproject.html'
	],
	[
		'icon' => 'fas fa-book',
		'title' => 'COM_JOOMPROJECT_PRODUCT_DOCS',
		'url' => 'https://www.joomboost.com/support/documentation/57-joomproject.html'
	],
	[
		'icon' => 'fas fa-sync-alt',
		'title' => 'COM_JOOMPROJECT_PRODUCT_CHANGELOG',
		'url' => 'https://www.joomboost.com/components-changelogs/103-joomproject-changelog.html'
	],
	[
		'icon' => 'fas fa-life-ring',
		'title' => 'COM_JOOMPROJECT_PRODUCT_TICKET',
		'url' => 'https://www.joomboost.com/support/submit-ticket.html'
	],
	[
		'icon' => 'fas fa-language',
		'title' => 'COM_JOOMPROJECT_PRODUCT_TRANSLATE',
		'url' => 'https://crowdin.com/project/joomproject'
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
				'url'   => $link['url'],
				'target' => '_blank',
			]
		) ?>
	<?php endforeach; ?>
</div>
