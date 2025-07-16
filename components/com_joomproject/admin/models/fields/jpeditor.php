<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\Field\EditorField;
FormHelper::loadFieldClass('Editor');


class JFormFieldJPEditor extends EditorField
{
    public $type = 'jpeditor';

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   3.5
     */
    protected function getRenderer($layoutId = 'default')
    {

        $renderer = new FileLayout($layoutId, null,
            [
                "client" => 1,
                'component' => 'com_joomproject'
            ]
        );

      /*  if(Factory::getApplication()->isClient('administrator')){
            $layoutPaths = $this->getLayoutPaths();

            if ($layoutPaths)
            {
                $renderer->setIncludePaths($layoutPaths);
            }
        } */

        return $renderer;
    }
}
