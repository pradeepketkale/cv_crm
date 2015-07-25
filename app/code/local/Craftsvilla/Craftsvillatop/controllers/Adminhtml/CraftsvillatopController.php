<?php

class Craftsvilla_Craftsvillatop_Adminhtml_CraftsvillatopController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('craftsvillatop/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('craftsvillatop'), Mage::helper('adminhtml')->__('craftsvillatop'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
 
}
