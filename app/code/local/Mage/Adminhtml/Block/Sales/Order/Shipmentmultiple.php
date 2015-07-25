<?php
class Mage_Adminhtml_Block_Sales_Order_Shipmentmultiple extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amit/shipment/shipment_multiple.phtml');
    }
	
    	protected function _prepareLayout()
    {
	
    }

        /* protected function getMultipleShipment()
	{
            //$shipment_id=$this->getRequest()->getParam('shipment_ids');
            //foreach($shipment_id as $shipmentId):
                return Mage::getModel('sales/order_shipment')->load($shipment_id);
            //endforeach;
            
	}	*/
	protected function getMultipleOrder($order_id)
	{
		return Mage::getModel('sales/order')->load($order_id);
		 //return Mage::registry('sales_order');
	}
}
?>