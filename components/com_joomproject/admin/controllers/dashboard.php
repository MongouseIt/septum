<?php
/**
 * @copyright	Copyright (c) 2013-2018 JoomBoost (https://www.joomboost.com). All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

/**
 * Testimonial Controller Class.
 *
 * @package		Joomla.Administrator
 * @subpakage	JoomBoost.JoomProject
 */
class JoomprojectControllerDashboard extends BaseController {
	
	public function addproject(){		
		
		$this->setRedirect('index.php?option=com_jpprojects&view=project&layout=edit');
		
	}
	
	public function addmilestone(){
		
		$this->setRedirect('index.php?option=com_jpmilestones&view=milestone&layout=edit');
		
	}
	
	public function addtask(){
		
		$this->setRedirect('index.php?option=com_jptasks&view=task&layout=edit');
		
	}
	
	
	public function addtopic(){
		
		$this->setRedirect('index.php?option=com_jpforum&view=topic&layout=edit');
		
	}
	
	public function adddesign(){
		
		$this->setRedirect('index.php?option=com_jpdesigns&view=design&layout=edit');
		
	}
	
	public function updateDownloadId(){
		
		Session::checkToken() or jexit('Invalid Token');
		
		$db = Factory::getDBO();
		
		$config = ComponentHelper::getParams('com_joomproject');
		
		$did = Factory::getApplication()->input->get('did','');
		
		// If the download ID is invalid, return without any further action
		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $did)) {			
			
			echo new JsonResponse('','Invalid ID',true);
			jexit();
		}
		
		
		$config->set('downloadid', $did);
		JoomprojectHelper::storeConfig($config);
		
		echo new JsonResponse('','Download ID updated');
		jexit();
		
	}
	
}