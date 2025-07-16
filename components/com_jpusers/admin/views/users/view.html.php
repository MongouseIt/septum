<?php
/**
 * @package      pkg_joomproject
 * @subpackage   com_joomproject
 *
 * @author       JoomBoost
 * @copyright    Copyright (C) 2012-2018 JoomBoost. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;

class JPusersViewRole extends HtmlView{

    function display($tpl = null){

        $this->form		= $this->get('Form');
        $this->item		= $this->get('Item');
        $this->state	= $this->get('State');
        $this->view		= Factory::getApplication()->input->get('view', '', 'cmd');
        $this->params	= ComponentHelper::getParams('com_joomproject');
        $this->canDo 	= ContentHelper::getActions('com_jpprojects');
        $this->isNew	= ($this->item->id == 0);

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        return parent::display($tpl);
    }

    protected function addToolbar(){

        ToolbarHelper::title(Text::_('COM_JOOMPROJECT_ROLES').' - '.Text::_('COM_JOOMPROJECT_'.($this->isNew ? 'NEW' : 'EDIT').'_'.strtoupper($this->view)),'user');

        if( $this->isNew ){

            if( $this->canDo->get('core.manage.roles') ){
                ToolbarHelper::apply($this->view.'.apply', 'JTOOLBAR_APPLY');
                ToolbarHelper::save($this->view.'.save',   'JTOOLBAR_SAVE');
                ToolbarHelper::divider();
                ToolbarHelper::custom($this->view.'.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
                ToolbarHelper::divider();
            }

        }else{

            if( $this->canDo->get('core.manage.roles') ){

                ToolbarHelper::apply($this->view.'.apply', 'JTOOLBAR_APPLY');
                ToolbarHelper::save($this->view.'.save',   'JTOOLBAR_SAVE');

                if( $this->canDo->get('core.manage.roles') ){
                    ToolbarHelper::divider();
                    ToolbarHelper::custom($this->view.'.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
                }
            }

            if( $this->canDo->get('core.manage.roles') ){
                ToolbarHelper::custom($this->view.'.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
                ToolbarHelper::divider();
            }
        }

        ToolbarHelper::cancel($this->view.'.cancel', 'JTOOLBAR_CANCEL');

        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_JOOMPROJECT').' - '.Text::_('COM_JOOMPROJECT_'.($this->isNew ? 'NEW' : 'EDIT').'_'.$this->view));
    }
}
