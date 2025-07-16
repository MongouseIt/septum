<?php
/**
 * @package      pkg_jpactivities
 * @subpackage   mod_jpactivities_site
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2013 JoomBoost.com. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;


HTMLHelper::_('bootstrap.tooltip');


Factory::getDocument()->addScript(Uri::root().'media/com_jpactivities/js/jquery.module.js');


$id = (int) $module->id;
$limit = (int) $params->get('list_limit');
$count = count($data['items']);
$start = 0;

$com_params = ComponentHelper::getParams('com_jpactivities');
$date_rel = $params->get('date_relative', $com_params->get('date_relative', 1));
$date_format = $params->get('date_format');

if (!$date_format) $date_format = Text::_('DATE_FORMAT_LC1');

$filter_ext = $params->get('filter_extension');
$ext_empty = empty($filter_ext);

if (!$ext_empty && is_array($filter_ext)) {
    $empty = true;
    foreach ($filter_ext AS $ext) {
        if (empty($ext)) continue;
        $empty = false;
    }
    $ext_empty = $empty;
}

$style = '.bg-project {'
    . 'background-color: #80699B;'
    . '}'
    . '.bg-milestone {'
    . 'background-color: #4572A7;'
    . '}'
    . '.bg-tasklist {'
    . 'background-color: #8bbc21;'
    . '}'
    . '.bg-file {'
    . 'background-color:  #dfe3e7;'
    . 'color:  black;'
    . '}'
    . '.bg-directory {'
    . 'background-color:  #d5e0eb;'
    . 'color:  black;'
    . '}'
    . '.bg-task {'
    . 'background-color: #910000;'
    . '}'
    . '.bg-time {'
    . 'background-color: #1aadce;'
    . '}'
    . '.bg-topic {'
    . 'background-color: #492970;'
    . '}'
    . '.bg-reply {'
    . 'background-color: #fc7136;'
    . '}'
    . '.bg-design {'
    . 'background-color: #f28f43;'
    . '}'
    . '.bg-category {'
    . 'background-color: #DB843D;'
    . '}'
    . '.bg-article {'
    . 'background-color: #95b262;'
    . '}'
    . '.row-striped .img-circle {'
    . 'margin: 0 10px 0 0;'
    . '}';

Factory::getDocument()->addStyleDeclaration($style);
?>
<script type="text/javascript">
    var fpv = '';

    function uaNext<?php echo $id;?>(el) {
        if (jQuery(el).hasClass('disabled') == false) {
            modUA.getItems('jpActivitiesForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'next');
        }
    }

    function uaPrev<?php echo $id;?>(el) {
        if (jQuery(el).hasClass('disabled') == false) {
            modUA.getItems('jpActivitiesForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'prev');
        }
    }

    function uaFilter<?php echo $id;?>() {
        modUA.getItems('jpActivitiesForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'filter');
    }

    function uaFilterSearch<?php echo $id;?>(v) {
        if (fpv == v) return;
        fpv = v;

        if (v.length > 2 || v.length == 0) {
            modUA.getItems('jpActivitiesForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'filter');
        }
    }
</script>
<div id="jb_template">
    <form action="<?php echo Route::_('index.php?option=com_jpactivities&view=module'); ?>" method="post"
          name="jpActivitiesForm<?php echo $id; ?>"
          id="jpActivitiesForm<?php echo $id; ?>"
          autocomplete="off"
    >

        <?php if ($count) :?>



                <!-- Start Search -->
                <?php if ($params->get('show_filter_search')) : ?>
                    <div class="input-group mb-3">
                        <?php if ($params->get('show_filter_extension') || $params->get('show_filter_event')) : ?>
                            <a role="button" class="btn btn-primary" data-bs-toggle="collapse" href="#act-filters-<?php echo $id; ?>">
                                <span aria-hidden="true" class="fas fa-filter"></span>
                            </a>
                        <?php endif; ?>
                        <input type="text" class="form-control search-query"
                               placeholder="<?php echo Text::_('MOD_JPACTIVITIES_SITE_FILTER_SEARCH_DESC'); ?>"
                               name="filter_search" value="" onkeyup="uaFilterSearch<?php echo $id; ?>(this.value);"
                               title="<?php echo Text::_('MOD_JPACTIVITIES_SITE_FILTER_SEARCH_DESC'); ?>"
                        />
                    </div>
                <?php endif; ?>
                <!-- End Search -->

            <div class="clearfix"></div>

            <!-- Start Filters -->
            <?php if ($params->get('show_filter_extension') || $params->get('show_filter_event')) : ?>

                <div class="collapse row" id="act-filters-<?php echo $id; ?>">
                        <?php
                        if ($params->get('show_filter_extension')) :
                            $ext = $params->get('filter_extension');
                            if ($ext_empty) :
                                ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select name="filter_extension" onchange="uaFilter<?php echo $id; ?>()" class="form-control">
                                            <option value=""><?php echo Text::_('MOD_JPACTIVITIES_SITE_FIELD_OPTION_SELECT_EXTENSION'); ?></option>
                                            <?php echo HTMLHelper::_('select.options', $model->getExtensions(), 'value', 'text', $ext); ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif;
                        endif;
                        ?>
                        <?php if ($params->get('show_filter_event')) : ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select name="filter_event_id" onchange="uaFilter<?php echo $id; ?>()"
                                            class="form-control">
                                        <option value=""><?php echo Text::_('MOD_JPACTIVITIES_SITE_FIELD_OPTION_SELECT_EVENT'); ?></option>
                                        <?php echo HTMLHelper::_('select.options', $model->getEvents(), 'value', 'text', $params->get('filter_event_id')); ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
            <?php endif; ?>
            <!-- End Filters -->
        <?php endif; ?>

        <!-- Start List -->
        <?php if ($count) : ?>
            <div id="activities-<?php echo $id; ?>" class="list-group list-group-flush list-group-striped">
                <?php
                foreach ($data['items'] as $i => $item) :

                    ?>

                    <?php echo LayoutHelper::render('event',['item' => $item,'params' => $params]) ?>

                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert"><?php echo Text::_('MOD_JPACTIVITIES_SITE_NO_MATCHING_RESULTS'); ?></div>
                </div>
            </div>
        <?php endif; ?>
        <!-- End List -->

        <!-- Start Bottom Navigation -->
        <?php if ($count) : ?>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <a class="actbtn-prev-<?php echo $id; ?> btn btn-sm disabled"
                       style="cursor: pointer;" onclick="uaPrev<?php echo $id; ?>(this);"
                    >
                        <span aria-hidden="true" class="fas fa-arrow-up"></span>
                    </a>
                    <a class="actbtn-next-<?php echo $id; ?> btn btn-sm <?php if ($limit >= $data['total']) echo ' disabled'; ?>"
                       style="cursor: pointer; " onclick="uaNext<?php echo $id; ?>(this);"
                    >
                        <span aria-hidden="true" class="fas fa-arrow-down"></span>
                    </a>
                </div>
            </div>
            <div class="clearfix"></div>
        <?php endif; ?>
        <!-- End Bottom Navigation -->

        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <input type="hidden" value="<?php echo $start; ?>" name="limitstart"/>
        <input type="hidden" value="<?php echo $limit; ?>" name="limit"/>
        <input type="hidden" value="<?php echo $data['total']; ?>" name="total"/>
        <input type="hidden" value="0" name="busy"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

