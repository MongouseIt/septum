<?php
/**
 * @package      Joomproject Pro
 * @subpackage   Designs
 *
 * @author       JoomBoost (eaxs)
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;





/**
 * Revision image class.
 *
 */
class JPdesignsViewRevision extends HtmlView
{
    /**
     * Display the view
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $item   = $this->get('Item');
        $params = ComponentHelper::getParams('com_jpdesigns', true);
        $layout = \Joomla\CMS\Factory::getApplication()->input->getCmd('layout', 'preview');

        // Permission check.
        if ($item->params->get('access-view') !== true) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage( Text::_('JERROR_ALERTNOAUTHOR'),'error');
            return false;
        }

        $options = array();

        if ($layout == 'preview') {
            $options['crop']    = true;
            $options['quality'] = 75;
            $options['size']    = $params->get('img_preview_size', '300x200');
        }
        else {
            $options['crop']    = false;
            $options['quality'] = 90;
            $options['size']    = $params->get('img_full_size', '960x540');
        }

        $source = JPdesignsHelper::getBasePath($item->project_id) . '/' . $item->file_name;
        $image = BaseDatabaseModel::getInstance('Image', 'JPdesignsModel', $options);

        $image->setSource($source);
        $image->setCacheId('revision', $item->project_id, $item->id);
        $image->setAuthor($item->author_name);
        $image->save();

        if ($image->isCached()) {
            Factory::getApplication()->redirect($image->getCachedURL());
        }
        else {
            $buffer = $image->getBuffer();

            if ($buffer) {
                header("Content-Type: image/jpeg");
        		header("Accept-Ranges: bytes");
        		header("Content-Length: " . filesize($image->getCachedFilePath()));
                echo $buffer;
            }
        }

        die();
    }
}
