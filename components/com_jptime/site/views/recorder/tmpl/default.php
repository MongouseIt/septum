<?php
/**
 * @package      Joomproject
 * @subpackage   Timetracking
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown');
HTMLHelper::_('bootstrap.collapse');
HTMLHelper::_('jphtml.script.form');
HTMLHelper::_('jphtml.script.listform');
HTMLHelper::_('jphtml.script.timerec');

$last_time = time() - $this->time;
if ($last_time > 60) $last_time = 0;

$pcfg = JPApplicationHelper::getProjectParams();
$currency_sign = $pcfg->get('currency_sign');
$currency_del  = $pcfg->get('decimal_delimiter');
$currency_pos  = $pcfg->get('currency_position');
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        JPtimerec.setForm('adminForm');
        JPtimerec.setTicker('ticker', 'ticker-progress');
        setInterval(JPtimerec.tick, 1000);
    });

    function setRateFieldValue(i)
    {
        var v1 = jQuery('#rec-rate0-' + i, JPtimerec.fe).val();
        var v2 = jQuery('#rec-rate1-' + i, JPtimerec.fe).val();

        jQuery('#rec-rate-' + i, JPtimerec.fe).val(v1 + '.' + v2);
    }
</script>
<div id="joomproject" class="view-recorder m-4">


        <h3><?php echo Text::_('COM_JOOMPROJECT_TIME_RECORDER_TITLE'); ?></h3>

    <form name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_jptime&view=recorder'); ?>"
          method="post"  autocomplete="off"
    >

        <table id="recordings" class="table border rounded">
            <thead class="bg-light">
            <tr>
                <th width="1" class="nowrap">
                    <div class="btn-group">
                        <a class="btn btn-light border btn-sm"
                           onclick="JPtimerec.pauseAll();"
                           title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_TIME_REC_TT_PAUSE_ALL')); ?>"
                           data-bs-toggle="tooltip"
                           data-placement="top"
                        >
                            <i class="fas fa-pause"></i>
                        </a>
                    </div>
                </th>
                <th width="1" class="nowrap">
                    <div class="btn-group">
                        <a class="btn btn-light border btn-sm"
                           onclick="JPtimerec.startAll();"
                           title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_TIME_REC_TT_RESUME_ALL')); ?>"
                           data-bs-toggle="tooltip"
                           data-placement="top"
                        >
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                </th>
                <th>
                    <div class="btn-group">
                        <a class="btn btn-success text-white"
                           onclick="JPtimerec.removeAll(1);"
                           title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_TIME_REC_TT_REMOVE_COMPLETE_ALL')); ?>"
                           data-bs-toggle="tooltip"
                           data-placement="top"
                        >
                            <i class="fas fa-check"></i>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a class="btn btn-danger text-white"
                           onclick="JPtimerec.removeAll(0);"
                           title="<?php echo addslashes(Text::_('COM_JOOMPROJECT_TIME_REC_TT_REMOVE_ALL')); ?>"
                           data-bs-toggle="tooltip"
                           data-placement="top"
                        >
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </th>
                <th style="vertical-align: middle; width:25%" class="nowrap">
                    <div class="progress active" id="ticker-progress" style="margin-bottom: 0px;">
                        <div class="progress-bar progress-bar-striped" style="width: 0%;"></div>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
			<?php
			$txt_desc_lbl = Text::_('COM_JOOMPROJECT_FIELD_DESCRIPTION_LABEL');
			$txt_rate_lbl = Text::_('COM_JOOMPROJECT_FIELD_RATE_LABEL');
			$txt_blb_lbl  = Text::_('COM_JOOMPROJECT_FIELD_BILLABLE_LABEL');
			$txt_no       = Text::_('JNO');
			$txt_yes      = Text::_('JYES');
			$txt_save     = Text::_('JSAVE');
			$txt_close    = Text::_('JLIB_HTML_BEHAVIOR_CLOSE');
			$txt_rm       = Text::_('COM_JOOMPROJECT_REMOVE');
			$txt_rmc      = Text::_('COM_JOOMPROJECT_TIME_REC_REMOVE_COMPLETE');

			foreach ($this->items AS $i => $item) :
				$id    = (int) $item['id'];
				$pause = (int) $item['pause'];
				$time  = (int) $item['time'];
				$data  = $item['data'];

				if ($time == 1) $time = 60;

				// Prepare rate field values


				list($rate_0, $rate_1) = explode('.', $data->rate);
				$rate = intval($rate_0) . '.' . intval($rate_1);

				// Prepare the Recording state button
				$btn_id    = 'btn-rec-state-' . $i;
				$btn_class = 'btn btn-sm btn-rec-state' . ($pause ? ' btn-light border' : ' btn-success active');
				$icon_class = 'fas' . ($pause ? ' fa-play' : ' fa-pause');
				$btn_js    = "JPtimerec.togglePause(" . $i . ");";

				$btn_pause = '<a href="javascript:void(0);" onclick="' . $btn_js . '" class="' . $btn_class . '" id="' . $btn_id . '">'
					. '    <i class="'.$icon_class.'"></i>'
					. '</a>';
				?>
                <tr class="row<?php echo $i % 2; ?> recording" id="rec-<?php echo $i; ?>">
                    <td class="nowrap">

                        <div class="dropdown d-block">
                            <button class="btn btn-light btn-sm border dropdown-toggle no-after" type="button" id="dropdownMenuButton-<?php echo $i; ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-<?php echo $i; ?>">
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="JPtimerec.remove(<?php echo $i; ?>, 0);">
                                        <i class="fas fa-times-circle me-1"></i> <?php echo $txt_rm; ?>
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item text-success" href="javascript:void(0);" onclick="JPtimerec.remove(<?php echo $i; ?>, 1);">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo $txt_rmc; ?>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </td>
                    <td class="nowrap">
                        <div class="btn-group mb-0">
							<?php echo $btn_pause; ?>
                        </div>
                    </td>
                    <td colspan="2">
                        <a data-bs-toggle="collapse" href="#rec-edit-<?php echo $i; ?>">
                            <strong><?php echo $this->escape($data->task_title); ?></strong>
                        </a>

                        <span class="border badge bg-light text-dark float-end" id="rec-time-<?php echo $id; ?>">
                            <?php echo HTMLHelper::_('time.format', $time); ?>
                        </span>
                        <div class="clearfix"></div>
                        <input type="hidden" name="pause[<?php echo $i; ?>]" id="rec-state-<?php echo $i; ?>" value="<?php echo $pause; ?>"/>
                        <div style="display: none;"><?php echo HTMLHelper::_('jp.html.id', $i, $id); ?></div>

                        <!-- Start Edit Container -->
                        <div class="collapse bg-light p-3 mt-2 rounded" id="rec-edit-<?php echo $i; ?>">
                            <div class="form-group">
                                <label for="rec-desc-<?php echo $i; ?>"><?php echo $txt_desc_lbl; ?></label>
                                <input
                                        type="text"
                                        id="rec-desc-<?php echo $i; ?>"
                                        class="form-control"
                                        name="description[<?php echo $i; ?>]"
                                        value="<?php echo $this->escape($data->description); ?>"
                                />
                            </div>
                            <div class="form-group">
                                <label for="rec-rate0-<?php echo $i; ?>"><?php echo $txt_rate_lbl; ?></label>

                                <div class="input-group" style="max-width: 250px">
	                                <?php if ($currency_pos == '0') : ?>
                                        <span class="input-group-text" id="rate"><?php echo $currency_sign; ?></span>
	                                <?php endif; ?>
                                    <input
                                            type="text"
                                            class="form-control"
                                            name="rate0[<?php echo $i; ?>]"
                                            id="rec-rate0-<?php echo $i; ?>"
                                           value="<?php echo (int) $rate_0; ?>"
                                            onkeyup="setRateFieldValue(<?php echo $i; ?>)"
                                            maxlength="5"
                                            aria-describedby="rate"
                                    />
                                    <div class="d-inline-block bg-light p-2 border-top border-bottom">
	                                    <?php echo $currency_del; ?>
                                    </div>
                                    <input
                                            type="text"
                                            class="form-control"
                                            name="rate1[<?php echo $i; ?>]"
                                            id="rec-rate1-<?php echo $i; ?>"
                                            value="<?php echo (int) $rate_1; ?>"
                                            onkeyup="setRateFieldValue(<?php echo $i; ?>)"
                                            maxlength="2"
                                    />
	                                <?php if ($currency_pos == '1') : ?>
                                        <span class="input-group-text" id="rate"><?php echo $currency_sign; ?></span>
	                                <?php endif; ?>
                                </div>
                            </div>
                            <input type="hidden" name="rate[<?php echo $i; ?>]" id="rec-rate-<?php echo $i; ?>"
                                   value="<?php echo $rate; ?>"/>

                            <div class="form-group">
                                <label><?php echo $txt_blb_lbl; ?></label>
                                <div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input" name="billable[<?php echo $i; ?>]"
                                               value="0" id="rec-blb0-<?php echo $i; ?>"
		                                    <?php echo (!$data->billable ? 'checked=checked' : ''); ?>
                                        />
                                        <label class="custom-control-label" for="rec-blb0-<?php echo $i; ?>"><?php echo $txt_no; ?></label>                                      </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input" name="billable[<?php echo $i; ?>]"
                                               value="1" id="rec-blb1-<?php echo $i; ?>"
		                                    <?php echo ($data->billable ? 'checked=checked' : ''); ?>
                                        />
                                        <label class="custom-control-label"  for="rec-blb1-<?php echo $i; ?>"><?php echo $txt_yes; ?></label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a class="btn btn-sm btn-success text-white" onclick="JPtimerec.save(<?php echo $i; ?>);" id="btn-rec-save-<?php echo $i; ?>">
			                        <i class="fas fa-edit"></i> <?php echo $txt_save; ?>
                                </a>
                                <a class="btn btn-sm btn-danger text-white" onclick="JPtimerec.closeEdit(<?php echo $i; ?>);">
                                    <i class="fas fa-times"></i> <?php echo $txt_close; ?>
                                </a>
                            </div>


                        </div>
                        <!-- End Edit Container -->
                    </td>

                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="complete" value="0" />
        <input type="hidden" name="ticker" id="ticker" value="<?php echo ($this->time == 0 ? 0 : $last_time); ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>

	<?php if (count($this->items)) : ?>
        <p class="alert alert-info">
			<i class="fas fa-info-circle"></i> <?php echo Text::_('COM_JOOMPROJECT_TIME_REC_NOTICE'); ?>
        </p>
	<?php else : ?>
        <div class="alert alert-warning">
			<i class="fas fa-exclamation-circle"></i> <?php echo Text::_('COM_JOOMPROJECT_TIME_REC_NOTICE_EMPTY'); ?>
        </div>
	<?php endif; ?>
</div>