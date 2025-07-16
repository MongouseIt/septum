<?php
/**
* @package      Joomproject Dashboard Buttons
*
* @author       JoomBoost
* @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (count($buttons) == 0) return '';

?>
<div id="joomproject">
            <div class="row">
            <?php foreach($buttons AS $component => $btns) : ?>
                <?php if (JPApplicationHelper::enabled($component)) : ?>
                    <?php foreach ($btns AS $btn) : ?>
                        <div class="col-md-2 text-center mb-2">
                            <a
                                href="<?php echo Route::_($btn['link']);?>"
                                class="w-100 h-100 p-3 btn btn-light text-dark border bg-light d-block"
                            >
	                            <span class="d-block mb-2"><?php echo $btn['icon']; ?></span>
                                <span class="d-block text-wrap"><i class="fas fa-plus"></i> <?php echo Text::_($btn['title']);?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
</div>

