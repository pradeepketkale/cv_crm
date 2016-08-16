<?php

class Craftsvilla_Customerlite_Adminhtml_CustomerliteController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerlite/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
 	public function gridAction()
	{
		 $this->loadLayout();
		 $this->getResponse()->setBody(
		 $this->getLayout()->createBlock('customerlite/adminhtml_customerlite_grid')->toHtml()
		 );
	}
	 public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$this->_redirect('kribhasanvi/customer/edit/',array('id' => $id));
    }
     public function newAction()
    {
       // $this->_forward('edit');
        $this->_redirect('kribhasanvi/customer/new');
    }
   
  

   
}	
