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
		'icon' => 'fab fa-facebook',
		'title' => 'Facebook Page',
		'url' => 'https://www.facebook.com/joomboost'
	],
	[
		'icon' => 'fab fa-twitter',
		'title' => 'Twitter Page',
		'url' => 'https://twitter.com/joomboost'
	],
	[
		'icon' => 'fab fa-linkedin',
		'title' => 'LinkedIn Company',
		'url' => 'https://www.linkedin.com/company/joomboost'
	],
	[
		'icon' => 'fab fa-pinterest',
		'title' => 'Pinterest',
		'url' => 'https://www.pinterest.com/joomboost/'
	],
	[
		'icon' => 'fas fa-rss',
		'title' => 'RSS Feed',
		'url' => 'https://feeds.feedburner.com/joomboost-news'
	],
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
