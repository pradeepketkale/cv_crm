<?php
class Mage_Adminhtml_Block_Sales_Order_Shipment extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amit/shipment/shipment.phtml');
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
		return Mage::getModel('sales/order')->load($order_id);
		 //return Mage::registry('sales_order');
	}	
}
?>