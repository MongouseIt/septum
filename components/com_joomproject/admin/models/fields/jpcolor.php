<?php
/**
 * @package		JoomProject
 * @copyright	2013-2019 JoomBoost, joomboost.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\Field\TextField;
FormHelper::loadFieldClass('Text');



class JFormFieldJPColor extends TextField
{
    protected $type = 'JPColor';

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

      Factory::getDocument()->addScript(Uri::root() . 'media/com_joomproject/joomproject/js/jscolor.js');
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
