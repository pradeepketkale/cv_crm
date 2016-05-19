<?php
class Craftsvilla_Seovendor_Adminhtml_SeovendorbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
			$this->loadLayout();
			$this->_title($this->__("Vendor Seo Data"));
			$this->renderLayout();
    }
		
		public function gridAction()
		{
			 $this->loadLayout();
			 $this->getResponse()->setBody(
			 $this->getLayout()->createBlock('seovendor/adminhtml_seovendor_grid')->toHtml()
			 );
		}
}