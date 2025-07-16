<?php
/**
* @package      Joomproject
* @subpackage   Library.html
*
* @author       JoomBoost
* @copyright    Copyright (C) 20012-2018 JoomBoost. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


abstract class JPhtmlButton
{
    public static function watch($type, $i, $state = 0, $options = array())
    {
        static $enabled = null;
        static $opt_out = null;

        if (is_null($enabled)) {
            $enabled = PluginHelper::isEnabled('content', 'jpnotifications');

            if ($enabled) {
                // Check the plugin access level
                $user = Factory::getApplication()->getIdentity();

                if (!$user->authorise('core.admin') && !$user->authorise('core.manage')) {
                    $db = Factory::getDbo();
                    $query = $db->getQuery(true);

                    $query->select('access')
                          ->from('#__extensions')
                          ->where('type = ' . $db->quote('plugin'))
                          ->where('element = ' . $db->quote('jpnotifications'))
                          ->where('folder = ' . $db->quote('content'));

                    $db->setQuery($query);
                    $plg_access = (int) $db->loadResult();
                    $levels     = $user->getAuthorisedViewLevels();
                    $enabled    = in_array($plg_access, $levels);
                }
            }
        }

        if (!$enabled) return '';

        if (is_null($opt_out)) {
            $plugin  = PluginHelper::getPlugin('content', 'jpnotifications');
            $params  = new Registry($plugin->params);
            $opt_out = (int) $params->get('sub_method', 0);
        }

        if ($opt_out) {
            $class = ($state == 1 ? ' btn-info' : ' btn-success active');
        }
        else {
            $class = ($state == 1 ? ' btn-success active' : ' btn-info');
        }

        $html      = array();
        $div_class = (isset($options['div-class']) ? ' ' . $options['div-class'] : '');
        $a_class   = (isset($options['a-class'])   ? ' ' . $options['a-class'] : '');

        $new_state = ($state == 1 ? 0 : 1);
        $aid       = 'watch-btn-' . $type . '-' . $i;
        $title     = addslashes(Text::_('COM_JOOMPROJECT_ACTION_WATCH_DESC'));

        //$html[] = '<div class="btn-group' . $div_class . '">';
        $html[] = '<a id="' . $aid . '"  data-bs-toggle="tooltip" data-placement="top"  class="btn' . $class . $a_class . '" title="' . $title . '" href="javascript:void(0);" ';
        $html[] = 'onclick="Joomproject.watchItem(' . $i . ', \'' . $type . '\')">';
        $html[] = '<span aria-hidden="true" class="fas fa-envelope"></span>';
        $html[] = '</a>';
        //$html[] = '</div>';
      // $html[] = '<div class="btn-group' . $div_class . '">';
        $html[] = '<input type="hidden" id="watch-' . $type . '-' . $i . '" value="' . (int) $state . '"/>';
      //  $html[] = '</div>';

        return implode('', $html);
    }


    public static function update()
    {
        // Load translations
		$basepath = JPATH_ADMINISTRATOR . '/components/com_joomproject/liveupdate';
		$lang     = Factory::getLanguage();

		$lang->load('liveupdate', $basepath, 'en-GB', true);
		$lang->load('liveupdate', $basepath, $lang->getDefault(), true);
		$lang->load('liveupdate', $basepath, null, true);

        $info = LiveUpdate::getUpdateInformation();
        $btn  = array();
        $html = array();

        if(!$info->supported) {
			// Unsupported
			$btn['class'] = 'btn-warning';
			$btn['icon']  = 'fas fa-exclamation-triangle';
			$btn['text']  = Text::_('LIVEUPDATE_ICON_UNSUPPORTED');
		}
        elseif($info->stuck) {
			// Stuck
			$btn['class'] = 'btn-danger';
			$btn['icon']  = 'fas fa-exclamation-triangle';
			$btn['text']  = Text::_('LIVEUPDATE_ICON_CRASHED');
		}
        elseif($info->hasUpdates) {
			// Has updates
			$btn['class']   = 'btn-primary';
			$button['icon'] = 'fas fa-download';
			$btn['text']    = Text::_('LIVEUPDATE_ICON_UPDATES');
		}
        else {
			// Already in the latest release
			$btn['class'] = 'btn-success';
			$btn['icon']  = 'fas fa-check';
			$btn['text']  = Text::_('LIVEUPDATE_ICON_CURRENT');
		}

        $html[] = '<a class="btn btn-sm ' . $btn['class'] . '"  data-bs-toggle="tooltip" data-placement="top"  title="Complete Task" href="index.php?option=com_joomproject&view=liveupdate">';
        $html[] = '<span aria-hidden="true" class="' . $btn['icon'] . '"></span> ';
        $html[] = $btn['text'];
        $html[] = '</a>';

        return implode('', $html);
    }
}