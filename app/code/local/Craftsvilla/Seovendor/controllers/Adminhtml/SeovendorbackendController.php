<?php
class Craftsvilla_Seovendor_Adminhtml_SeovendorbackendController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		 $this->loadLayout()
					->_setActiveMenu("seovendor/seovendor")
					->_addBreadcrumb(Mage::helper("adminhtml")->__("VendorSeo  Manager"),Mage::helper("adminhtml")->__("VendorSeo Manager"));
					return $this;
	}
	
	public function indexAction() 
	{
			$this->_title($this->__("Manager Vendorseo"));
			$this->_initAction();
			$this->renderLayout();
	}
}