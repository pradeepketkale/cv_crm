<?php

class Craftsvilla_Financereport_Model_Financereport extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('financereport/financereport');
    }

public function reportcod(){
	
	//$fromdDate = $this->getRequest()->getParam('from_date');
	//$todDate = $this->getRequest()->getParam('to_date');
	//$payoutStatus = $this->getRequest()->getParam('payoutstatus');
	//$courierName = $this->getRequest()->getParam('courier');
	
	
	
	

	if($payoutStatus == 'unpaid'){ $payoutStatus1 = 0;}
	if($payoutStatus == 'paid'){ $payoutStatus1 = 1;}
	if($payoutStatus == 'refund'){ $payoutStatus1 = 2;}
	


	if($fromdDate && $todDate)
		{
		    $whereSelect = " AND sfo.`created_at` >= '".$fromdDate."' AND sfo.`created_at` <= '".$todDate."'";
		}
	if($payoutStatus)
		{
			$whereSelect1 = " AND sp.`shipmentpayout_status` =".$payoutStatus1;
		}
	if($courierName)
		{
			$whereSelect2 = " AND sfst.`courier_name` ='%".$courierName."%'";
		}
	
	
	
	$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
	$codQuery =	"SELECT sfo.`increment_id` AS order_id, sfo.`created_at` AS order_date, sfs.`increment_id` AS shipment_id, sfs.`created_at` AS shipment_datec, sfst.`number` AS awb_number, sfs.`updated_at` AS shipment_update, sp.`shipmentpayout_created_time` AS payment_created_date, sp.`shipmentpayout_update_time` AS payment_updated_date, uv.`vendor_name` AS vendor_name, sfs.`base_total_value` as SubTotal, sp.`payment_amount` AS payment_amount, sp.`commission_amount` AS comission_amount
FROM `sales_flat_shipment` as sfs
LEFT JOIN `sales_flat_order` AS sfo ON `sfs`.`order_id` = `sfo`.`entity_id`
LEFT JOIN `sales_flat_shipment_track` AS sfst ON `sfs`.`entity_id` = `sfst`.`parent_id`
LEFT JOIN `shipmentpayout` AS sp ON `sfs`.`increment_id` = `sp`.`shipment_id`
LEFT JOIN `udropship_vendor` AS uv ON `sfs`.`udropship_vendor` = `uv`.`vendor_id`
LEFT JOIN `sales_flat_order_payment` AS sfop ON `sfo`.`entity_id` = `sfop`.`parent_id`
WHERE sfop.`method` = 'cashondelivery'". $whereSelect . $whereSelect1 . $whereSelect2;

$resultCodData = $readQuery->query($codQuery)->fetchAll();

return json_encode($resultCodData);

	//foreach($resultCodData as $_resultCodData)
	//{

		//echo $_resultCodData['order_id'];exit;

	//}
}


}
