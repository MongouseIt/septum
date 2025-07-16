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

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

extract($displayData);


// Deadline and completition date
$date = HTMLHelper::_('jphtml.label.datetime', ($item->complete ? $item->completed : $item->end_date), true, ($item->complete ? array('past-class' => 'text-dark', 'past-icon' => 'calendar') : array()));

$show_date = $params->get('show_deadline');
$show_assigned = $params->get('show_assigned');
$show_priority = $params->get('show_priority');


// enable quick log time
$enable_quicklog_time = $params->get('enable_quicklogtime', 0);

if ($enable_quicklog_time) {
    // alerts container
    echo LayoutHelper::render('common.alertscontainer', null, '', ['component' => 'com_joomproject', 'client' => 'admin']);
    // quick log time js code
    Factory::getDocument()->addScript(Uri::root() . 'media/com_joomproject/joomproject/js/quicklog.js');
}


if ($show_priority) :
    $doc = Factory::getDocument();
    $style = "
 .JPTasks .row-fluid{ padding: 5px 10px !important;}
.complete {
            opacity:0.5;
            }
            .task-title > a {
            margin-left:10px;
            margin-right:10px;
            }
            .margin-none {
            margin: 0;
            }
            .priority-1 {
            border-left:2px solid #CCC;
            }
            .priority-2 {
            border-left:2px solid #468847;
            }
            .priority-3 {
            border-left:2px solid #3a87ad;
            }
            .priority-4 {
            border-left:2px solid #c09853;
            }
            .priority-5 {
            border-left:2px solid #b94a48;
            }
            .row-striped.row-tasks {
            line-height: 30px;
            }
            .row-striped .img-circle {
            margin: 0 10px 0 0;
            }
    ";
    $doc->addStyleDeclaration($style);
endif;


?>

<?php if ($show_assigned) :
    foreach ($item->users as $usr) :
        ?>
        <img title="<?php echo $usr->name; ?>"
             data-bs-toggle="tooltip" data-placement="top"
             width="25"
             src="<?php echo HTMLHelper::_('joomproject.avatar.path', $usr->user_id); ?>"
             class="rounded-circle float-start me-1 shadow-sm width-30"
        />
    <?php endforeach; ?>
<?php endif; ?>
<span class="task-name item-name">
                            <?php
                            if ($show_date):
                                if ($item->complete):
                                    echo '<i class="fas fa-check-square"></i>';
                                else :
                                    echo '<i class="far fa-square"></i>';
                                endif;
                            endif;
                            ?>
                            <a href="<?php echo Route::_(JPtasksHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug)); ?>">
                                <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </span>
<?php if ($enable_quicklog_time): ?>
    <div class="btn-group d-inline float-end my-0 ms-2">
        <button class="btn-light btn-sm text-muted btn dropdown-toggle" type="button" id="taskactions"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <?php echo LayoutHelper::render('task.quicklogtime', ['task' => $item], '', ['component' => 'com_jptasks']) ?>
        </div>
    </div>
<?php endif; ?>
<?php
if ($show_date) :
    // echo '<span class="float-end small muted">' . HTMLHelper::_('date', $item->end_date, Text::_('M d')) . '</span>';
    echo '<div class="float-end">' . $date . '</div>';
endif;
?>
