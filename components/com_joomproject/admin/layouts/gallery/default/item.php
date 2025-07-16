<?php

/**
 * @package      Joomproject
 * @subpackage   Projects
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

extract($displayData);

?>
<figure
    class="item"
    <?php echo $tags_position !== 'disabled' && isset($item['tags']) && is_array($item['tags']) && count($item['tags']) ? ' data-tags="' . htmlentities(json_encode($item['tags']), ENT_COMPAT, 'UTF-8') . '"' : ''; ?>
>
    <?php if ($lightbox) { ?>
        <a href="<?php echo $item['url']; ?>" class="tf-gallery-lightbox-item <?php echo $id; ?>" data-type="image" data-description=".glightbox-desc.<?php echo $id; ?>.desc-<?php echo $item['index']; ?>">
    <?php } ?>
        <img<?php echo $style === 'justified' ? '' : ' loading="lazy"'; ?> class="<?php echo ""//$thumb_class ?>" src="<?php echo $item['thumbnail_url']; ?>"<?php echo $item['alt']; ?> alt="<?php echo strip_tags($item['alt']); ?>" />
    <?php if ($lightbox) { ?>
        </a>
        <div class="glightbox-desc <?php echo $id . ' desc-' . $item['index']; ?>">
            <div class="caption"><?php echo nl2br($item['caption']); ?></div>
            <div class="module"><?php echo !empty($module) ? JoomprojectGallery::loadModule($module) : ''; ?></div>
        </div>
    <?php } ?>
</figure>