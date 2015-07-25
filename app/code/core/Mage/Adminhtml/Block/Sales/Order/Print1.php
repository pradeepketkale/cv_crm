<?php
class Mage_Adminhtml_Block_Sales_Order_Print1 extends Mage_Core_Block_Template
{
	public function __construct()
    {
    	
    	parent::__construct();
    	//Mage::log($this);
    	$this->setTemplate('sales/order/print1.phtml');
    	
    }
	
	protected function _prepareLayout()
    {
	
    }

	
	protected function getOrder()
	{
		$order_id=$this->getRequest()->getParam('order_ids');
		return Mage::getModel('sales/order')->loadByIncrementId($order_id);
		 //return Mage::registry('sales_order');
	}	
}