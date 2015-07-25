<?php

class Mage_Adminhtml_Block_Renderer_Country extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row)
    {
    	$rec = $row->getData();
        $orders=Mage::getModel('sales/order')->loadByIncrementId($rec['increment_id']);
        $country=$orders->getShippingAddress()->getCountryId();
        /*foreach($orders as $k=>$v) {
            $country = $v->getShippingAddress()->getCountryId();
    	}   	*/
        return $country;
    }

}

class Mage_Adminhtml_Block_Renderer_Escalate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row)
    {
    	$rec = $row->getData();
    	//echo "SSSS:".$rec['entity_id'];
    	$order_collection = Mage::getModel('sales/order_address')->getCollection();
		$order_collection->addFieldToFilter('parent_id',$rec['entity_id'])
						 ->addFieldToFilter('address_type','shipping');
		$order_collection_country = $order_collection->getData();
		$order_collection_country_val = $order_collection_country[0]['country_id'];
		
		if($order_collection_country_val != "IN")
		{
			$shipment_status_arr = array();
			$get_shipment_adr_col = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('order_id',$rec['entity_id']);
    		$get_shipment_adr_arr = $get_shipment_adr_col->getData();
    		if($get_shipment_adr_arr)
    		{
    			foreach($get_shipment_adr_arr as $get_shipment_adr_arr_val)
    			{
    				$shipment_status_arr[] = $get_shipment_adr_arr_val['udropship_status'];		
    			}

    			$shipment_status_value = "Cancelled";
    		
	    		foreach($shipment_status_arr as $shipment_status_val)
	    		{
	    			if($shipment_status_val == 11 || $shipment_status_val == 16 || $shipment_status_val == 15)
	    			{
	    				return "<span style='color:#0000FF;'><b>Waiting For Shipment</b></span>";
	    			}	
	    		}
	    		
	    		foreach($shipment_status_arr as $shipment_status_val)
	    		{
	    			if($shipment_status_val == 1)
	    			{
	    				$shipment_status_value = "Shipped";
	    			}
	    			else if($shipment_status_val != 6 && $shipment_status_val != 12 && $shipment_status_val != 14)
	    			{
	    				$shipment_status_value = "Ready To Ship";
	    				break;
	    			}	
	    		}
	    		
	    		return "<span style='color:#00FF00;'><b>".$shipment_status_value."</b></span>";
    		}
		}
    }
}

?>