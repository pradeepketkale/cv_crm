<?php 
class Craftsvilla_Codshipments_Block_Adminhtml_Renderer_Amount extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		
		$getData = $row->getData();
		$entityid = $getData['entity_id'];
		$totalvalue = $getData['base_total_value'];
		$itemisedcost = $getData['itemised_total_shippingcost'];
		$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
		$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$entityid)
								  ->columns('SUM(a.base_discount_amount) AS amount');
					//echo $orderitem->getSelect()->__toString();			  exit;
			$orderitemdata = $orderitem->getData();
			foreach($orderitemdata as $_orderitemdata)
			{
			 $discountamount = $_orderitemdata['amount'];
			}	
			
			 $vendorid=$getData['udropship_vendor'];
			 $vendor = Mage::getModel('udropship/vendor')->load($vendorid);
			 $custom = Zend_Json::decode($vendor->getCustomVarsCombined());
		     $codfee = $custom['cod_fee'];
		return $amountCOD = $totalvalue + $itemisedcost - $discountamount + $codfee;	
    }
    
    
}
?>