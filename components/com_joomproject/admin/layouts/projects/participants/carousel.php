<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2019 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
# No Permission
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

// init

$params       = $displayData['params'];
$data       = $displayData['data'];
$modId         =  $displayData['modid'];
$text = $params->get('userType',0) == 0 ? 'PARTICIPANTS' : 'ASSIGNED';

JLoader::register('JPusersHelperRoute',JPATH_ROOT.'/components/com_jpusers/helpers/route.php');

HTMLHelper::_('script', 'com_joomproject/joomproject/swiper.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/swiper.css');
// Swiper params
$effect                = $params->get('effect','slide');
$loop                  = $params->get('loop','true');
$grabcursor            = $params->get('grabcursor','true');
$loopblank             = $params->get('loopblank','true');
$bar                   = $params->get('bar','pagination');
$navbuttons            = $params->get('navbuttons','1');
$item480               = $params->get('item480',1);
$item768               = $params->get('item768',2);
$item1024              = $params->get('item1024',3);
$navbuttonscolor        = $params->get('navbuttons_color','');

if(!empty($navbuttonscolor)){

    Factory::getDocument()->addStyleDeclaration("
        .swiper-button-next,.swiper-button-prev{            
            color: $navbuttonscolor;        
        }
    ");

}

$js_swiper =
    <<<EOF
            window.addEventListener('load',function(){
            
     var jrCarousel$modId = new Swiper('#jrItems-$modId .swiper-container', {
        navigation: {
            nextEl: '#jrItems-$modId  .swiper-button-next',
            prevEl: '#jrItems-$modId  .swiper-button-prev',
        },
        scrollbar: {
            el: '#jrItems-$modId .swiper-scrollbar',
            hide: false,
        },
        slidesPerView: $item1024,
        spaceBetween: 30,
        effect: '$effect',
        grabCursor: $grabcursor,
        centeredSlides: false,        
        coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows : true,
        },
        fadeEffect: {
            crossFade: true
        },
        loop: $loop,
        loopFillGroupWithBlank: $loopblank,
        pagination: {
        el: '#jrItems-$modId .swiper-pagination',
        dynamicBullets: true,
      },
        breakpoints: {
            480: {
              slidesPerView: $item480,
              spaceBetween: 10,
            },
            768: {
              slidesPerView: $item768,
              spaceBetween: 20,
            },
            1024: {
              slidesPerView: $item1024,
              spaceBetween: 30,
            }
  }    
    });
       
    });
EOF;
Factory::getDocument()->addScriptDeclaration($js_swiper);


?>
<?php if($params->get('userType',0) == 0 && (!ComponentHelper::isInstalled('com_jpactivities') OR !ComponentHelper::isEnabled('com_jpactivities'))): ?>

    <div class="alert alert-warning">
        <?php echo Text::_('COM_JPPROJECT_COM_JPACTIVITIES_NOT_INSTALLED_OR_ENABLED'); ?>
    </div>
<?php else : ?>
<?php  if (count($data) > 0 ) : ?>
    <div id="jrItems-<?php echo $modId; ?>" class="mb-4">
        <div class="swiper-container" id="joomproject">
            <div class="swiper-wrapper px-3">
                <?php foreach ($data as $i => $participant) :
                    $user = $participant->username;
                    $link = "index.php?option=com_jpusers&view=user&id=$participant->id:$user";
                    ?>
                    <div class="swiper-slide">
                        <a style="background-color:<?php echo $participant->backgroundColor;?>"
                           title="<?php echo $participant->name;?>" data-bs-toggle="tooltip" data-placement="top"
                           class="img-users shadow-sm rounded-circle text-decoration-none text-center d-inline-block text-white"
                           href="<?php echo \Joomla\CMS\Router\Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
                            <?php echo $participant->img ?>
                        </a>
                        <span class="d-inline-block  ms-2">
                            <a href="<?php echo \Joomla\CMS\Router\Route::_(JPusersHelperRoute::getUserRoute($participant->link));?>">
                                <?php echo $participant->name; ?>
                            </a>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
            if($bar == 2){
                echo '<div class="swiper-scrollbar"></div>';
            }
            elseif($bar == 1){
                echo '<div class="swiper-pagination"></div>';
            }
            if($navbuttons == 1){ ?>
                <div class="swiper-button-prev"><?php echo $params->get('nav_prev','❮'); ?></div>
                <div class="swiper-button-next"><?php echo $params->get('nav_next','❯'); ?></div>
            <?php } ?>
        </div>
    </div>
<?php else : ?>
    <div class="alert alert-warning"><?php echo Text::_('MOD_JPPROJECT_NO_'.$text.'_MATCHING_RESULTS'); ?></div>
<?php endif; ?>
<?php endif; ?>



