<?php
class Unirgy_Dropship_Block_Vendor_Shipment_Print extends Mage_Core_Block_Template
{
	public function __construct()
    {
    	parent::__construct();
    	//Mage::log($this);
    	$this->setTemplate('marketplace/shipment/print.phtml');
    	
    }
	
	protected function _prepareLayout()
    {
	
    }

	protected function getShipment()
	{
		$shipment_id=$this->getRequest()->getParam('shipment_id');
		return Mage::getModel('sales/order_shipment')->load($shipment_id);
	}	
	protected function getOrder()
	{
		$order_id=$this->getRequest()->getParam('order_id');
		return Mage::getModel('sales/order')->loadByIncrementId($order_id);
		 //return Mage::registry('sales_order');
	}	
}