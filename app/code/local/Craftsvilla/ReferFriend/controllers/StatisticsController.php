<?php 
	class Craftsvilla_ReferFriend_StatisticsController extends Mage_Core_Controller_Front_Action
	{
		public function indexAction(){
			$session = $this->_getSession();
			if (!$session->isLoggedIn()) {
				$this->_redirectUrl($this->_getLoginUrl());
				return;
			}
			
			$this->loadLayout();
			$this->renderLayout();
		}
		
		protected function _getSession()
		{
			return Mage::getSingleton('customer/session');
		}
		
		protected function _getBaseUrl(){
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);	
		}
		
		protected function _getLoginUrl(){
			return Mage::Helper('customer/data')->getLoginUrl();	
		}
	}
?>