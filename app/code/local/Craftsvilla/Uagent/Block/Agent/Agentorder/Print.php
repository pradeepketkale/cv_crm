<?php
class Craftsvilla_Uagent_Block_Agent_Agentorder_Print extends Mage_Core_Block_Template
{
	public function __construct()
    {
    	parent::__construct();
    	//Mage::log($this);
    	$this->setTemplate('uagent/agentorder/print.phtml');
    	
    }
	
	protected function _prepareLayout()
    {
	
    }

		
	protected function getOrder()
	{
		$order_id=$this->getRequest()->getParam('order_id');
		return Mage::getModel('sales/order')->loadByIncrementId($order_id);
		 //return Mage::registry('sales_order');
	}	
}