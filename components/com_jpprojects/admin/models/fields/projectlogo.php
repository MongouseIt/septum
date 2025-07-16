<?php
/**
 * @package      Joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\FormField;


defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for uploading a project logo
 *
 */
class JFormFieldProjectLogo extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'ProjectLogo';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        $html = $this->getHTML();

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string              The html field markup
     */
    protected function getHTML()
    {
        if (!$this->value) {
            $this->value = (int) $this->form->getValue('id');
        }

        if (Factory::getApplication()->isClient('site') || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML();
        }

        return $this->getAdminHTML();
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML()
    {
        $html = array();

        $base_url  = Uri::root(true) . '/media/com_joomproject/repo/0/logo';
        $base_path = JPATH_ROOT . '/media/com_joomproject/repo/0/logo';
        $img       = (int) $this->value;
        $img_url   = null;

        if (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.jpg')) {
            $img_url = $base_url . '/' . $img . '.jpg';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.jpeg')) {
            $img_url = $base_url . '/' . $img . '.jpeg';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.png')) {
            $img_url = $base_url . '/' . $img . '.png';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.gif')) {
            $img_url = $base_url . '/' . $img . '.gif';
        }

        if ($img_url) {
            $html[] = '<img class="thumbnail shadow-sm" src="' . $img_url . '" style="max-width:160px;max-height:100px"/>';
            $html[] = '<div class="form-check"> <input type="checkbox" name="' . $this->name . '[delete]" value="1" class="form-check-input" id="pojectLogoDelete"/><label class="form-check-label" for="pojectLogoDelete">' . Text::_('JACTION_DELETE_IMAGE') . '</label></div>';
        }

        $html[] = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"/>';

        return $html;
    }



    /**
     * Method to generate the frontend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML()
    {
        $html = array();

        $base_url  = Uri::root(true) . '/media/com_joomproject/repo/0/logo';
        $base_path = JPATH_ROOT . '/media/com_joomproject/repo/0/logo';
        $img       = (int) $this->value;
        $img_url   = null;

        if (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.jpg')) {
            $img_url = $base_url . '/' . $img . '.jpg';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.jpeg')) {
            $img_url = $base_url . '/' . $img . '.jpeg';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.png')) {
            $img_url = $base_url . '/' . $img . '.png';
        }
        elseif (\Joomla\CMS\Filesystem\File::exists($base_path . '/' . $img . '.gif')) {
            $img_url = $base_url . '/' . $img . '.gif';
        }

        if ($img_url) {
            $html[] = '<div><img class="thumbnail shadow-sm" src="' . $img_url . '" style="max-width:160px;max-height:100px"/></div>';
            $html[] = '<div class="form-check d-block"> <input id="pojectLogoDelete" type="checkbox" name="' . $this->name . '[delete]" value="1"/><label class="form-check-label ms-1 text-danger" for="pojectLogoDelete">' . Text::_('JACTION_DELETE_IMAGE') . '</label></div>';
            $html[] = '<div class="clearfix"></div>';
        }



        $html[] = '<div class="my-3">';
        $html[] = '<input type="file" class="form-control form-control-sm" name="' . $this->name . '" id="' . $this->id . '"/>';


        $html[] = '</div>';

        return $html;
    }

    protected function getRenderer($layoutId = 'default')
    {

        $renderer = new FileLayout($layoutId, null,
            [
                "client" => 1,
                'component' => 'com_joomproject'
            ]
        );


        return $renderer;
    }
}