<?php
class Craftsvilla_Shipmentpayout_Block_Renderer_Subtotal extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row)
    {
	 	$_liveDate = "2012-08-21 00:00:00";
		$order = Mage::getModel('sales/order')->loadByIncrementId($row->getOrderId());
		$_orderCurrencyCode = $order->getOrderCurrencyCode();
		if(($_orderCurrencyCode != 'INR') && (strtotime($row->getOrderCreatedAt()) >= strtotime($_liveDate))){
			return $row->getSubtotal()/1.5;
		}
		return $row->getSubtotal();
    }
}
?>
