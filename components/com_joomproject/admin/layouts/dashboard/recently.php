<?php
/**
 * @package        JoomProject
 * @copyright      2013-2019 JoomBoost, joomboost.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

HTMLHelper::_('stylesheet', 'com_joomproject/joomproject/Chart.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_joomproject/joomproject/moment.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_joomproject/joomproject/Chart.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_joomproject/joomproject/Chart.utils.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_joomproject/joomproject/Chart.bundle.min.js', array('version' => 'auto', 'relative' => true));

?>
<style>
    #joomproject .bg-blue {
        background-color: rgb(54, 162, 235);
    }

    #joomproject .bg-red {
        background-color: rgb(255, 99, 132);
    }

    #joomproject .bg-orange {
        background-color: rgb(255, 159, 64);
    }

    #joomproject .bg-yellow {
        background-color: rgb(255, 205, 86);
    }

    #joomproject .bg-green {
        background-color: rgb(75, 192, 192);
    }

    #joomproject .bg-purple {
        background-color: rgb(153, 102, 255);
    }

    #joomproject .bg-grey {
        background-color: rgb(201, 203, 207);
    }

    #joomproject .border-left-dotted{
        border-left: 2.5px dotted #dee2e6 !important;

    }
</style>
<canvas id="chart_recent_items"></canvas>
<script>
    var daysRange = numberRange(-29, 1);
    var timeFormat = 'MM/DD';
    var color = Chart.helpers.color;
    var config = {
        type: 'line',
        data: {
            labels: daysRange,
            datasets: [
				<?php foreach($recent as $line): ?>
                {
                    label: '<?php echo $line['title'] ?>',
                    backgroundColor: color(window.chartColors.<?php echo $line['color']?>).alpha(0.5).rgbString(),
                    borderColor: window.chartColors.<?php echo $line['color']?>,
                    fill: false,
                    data: [
						<?php echo implode(',', $line['data']) ?>
                    ]
                },
				<?php endforeach; ?>

            ]
        },
        options: {

            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        parser: timeFormat,
                        // round: 'day'
                        tooltipFormat: 'll'
                    },
                    scaleLabel: {
                        display: true,
                        labelString: '<?php echo Text::_('COM_JOOMPROJECT_GRAPH_DATE'); ?>'
                    }
                }],
                yAxes: [
                    {
                    ticks: {
                        suggestedMin: 0,    // minimum will be 0, unless there is a lower value.
                        beginAtZero: true,   // minimum value will be 0.
                        stepSize: 1,

                    },
                    scaleLabel: {
                        display: true,
                        labelString: '<?php echo Text::_('COM_JOOMPROJECT_GRAPH_SUBMISSION'); ?>'
                    }
                }]
            },
        }
    };

    window.onload = function () {
        var ctx = document.getElementById('chart_recent_items').getContext('2d');
        window.myLine = new Chart(ctx, config);

    };

</script>


