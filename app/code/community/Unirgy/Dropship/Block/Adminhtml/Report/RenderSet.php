<?php
class Unirgy_Dropship_Block_Adminhtml_Report_RenderSet extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$Row_Id = $row->getId();
		unset($comments);
		unset($comments_arr_val);
		unset($latest_comment_val);
		unset($shipment_created_date);
		
		$shipment_status = array('Shipped To Customer', 'Delivered', 'Processing', 'Refund Initiated','Partially Refund Initiated', 'Charge Back', 'Shipped To Craftsvilla', 'QC Rejected by Craftsvilla', 'Received in Craftsvilla', 'Shipped', 'Canceled','Product Out Of Stock','Dispute Raised','Shipment Delayed','Partially Shipped','Accepted');
		
		$get_shipment_adr_col = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('main_table.entity_id',$Row_Id);
		
		//echo "<pre>";
		//print_r($get_shipment_adr_col->getData());
		$shipment_arr_val = $get_shipment_adr_col->getData();
		$shipment_created_date = $shipment_arr_val[0]['created_at'];
		/*$comments = Mage::getModel('sales/order_shipment_comment')->getCollection()
																  ->addFieldToFilter('parent_id',$Row_Id)	
																  ->setOrder('entity_id', 'DESC')
																  ->setPageSize(1);*/
		if($comments)
		{
			//echo "Bow Bow:<pre>";
			$comments_arr_val = $comments->getData();
			//print_r($comments_arr_val);
			$latest_comment_val = $comments_arr_val[0]['udropship_status'];
			if(in_array($latest_comment_val, $shipment_status))
			{
				if($latest_comment_val == 'Shipped To Customer' || $latest_comment_val == 'Delivered' || $latest_comment_val == 'Shipped')
				{
					return "<span style='color:#8ede26;'><b>Shipped</b></span>";
				}
				else if($latest_comment_val == 'Refund Initiated' || $latest_comment_val == 'Canceled' || $latest_comment_val == 'Charge Back' || $latest_comment_val == 'Partially Refund Initiated' || $latest_comment_val == 'Dispute Raised' || $latest_comment_val == 'Shipment Delayed' || $latest_comment_val == 'Partially Shipped')
				{
					return "<span style='color:#000;'><b>".$latest_comment_val."</b></span>";
				}
				else
				{
					unset($datediff);
					unset($your_date);
					unset($no_days);
					unset($to_day_date);
					unset($shipment_date);
					$shipment_date = date('Y-m-d h:i:s', strtotime(trim($shipment_created_date)));
					$to_day_date = time();
					$your_date = strtotime($shipment_date);
	     			$datediff = ($to_day_date - $your_date);
	     			$no_days = floor(($datediff)/(60*60*24));
	     			if($no_days<5)
	     			{
	     				return "<span style='color:#FF64F2;'><b>Waiting to be shipped</b></span>";
	     			}
	     			else if($no_days>5 && $no_days<=10) {
	     				return "<span style='color:#A52A2A;'><b>Delayed</b></span>";
	     			}
					else if($no_days>10 && $no_days<=15) {
	     				return "<span style='color:#FFA836;'><b>Super Delayed</b></span>";
	     			}
					else if($no_days>15) {
	     				return "<span style='color:#FF0000;'><b>Need to ESCALATE</b></span>";
	     			}
				}
			}
			else 
			{
				//echo "Status:".$shipment_arr_val[0]['udropship_status'];
				if($shipment_arr_val[0]['udropship_status'] == 6 || $shipment_arr_val[0]['udropship_status'] == 14 || $shipment_arr_val[0]['udropship_status'] == 12)
				{
					return "<span style='color:#000;'><b>Cancelled</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 1 || $shipment_arr_val[0]['udropship_status'] == 7)
				{
					return "<span style='color:#8ede26;'><b>Shipped</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 12)
				{
					return "<span style='color:#8ede26;'><b>Refund Initiated</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 16)
				{
					return "<span style='color:#8ede26;'><b>QC Rejected by Craftsvilla</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 18)
				{
					return "<span style='color:#8ede26;'><b>Product Out Of Stock</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 19)
				{
					return "<span style='color:#8ede26;'><b>Partially Refund Initiated</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 20)
				{
					return "<span style='color:#8ede26;'><b>Dispute Raised</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 21)
				{
					return "<span style='color:#8ede26;'><b>Shipment Delayed</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 22)
				{
					return "<span style='color:#8ede26;'><b>Partially Shipped</b></span>";
				}
				else if($shipment_arr_val[0]['udropship_status'] == 24)
				{
					return "<span style='color:#8ede26;'><b>Accepted</b></span>";
				}
				else {
					unset($datediff);
					unset($your_date);
					unset($no_days);
					unset($to_day_date);
					unset($shipment_date);
					$shipment_date = date('Y-m-d h:i:s', strtotime(trim($shipment_created_date)));
					$to_day_date = time();
					$your_date = strtotime($shipment_date);
		     		$datediff = ($to_day_date - $your_date);
		     		$no_days = floor(($datediff)/(60*60*24));
		     		if($no_days<5)
		     		{
		     			return "<span style='color:#FF64F2;'><b>Waiting to be shipped</b></span>";
		     		}
		     		else if($no_days>5 && $no_days<=10) {
		     			return "<span style='color:#A52A2A;'><b>Delayed</b></span>";
		     		}
					else if($no_days>10 && $no_days<=15) {
		     			return "<span style='color:#FFA836;'><b>Super Delayed</b></span>";
		     		}
					else if($no_days>15) {
		     			return "<span style='color:#FF0000;'><b>Need to ESCALATE</b></span>";
		     		}	
				}
			}
		}														  
																  
		//exit();
		
		/*
		 * Status
		 * Processing, Shipped To Craftsvilla, QC Rejected by Craftsvilla, Received in Craftsvilla
		 * 
		 */
		/*echo "<pre>";
		print_r($comments_arr_val);
		exit();*/
		
		//echo $comments->getSelect()->__toString()."<br />";

		/*if($comments_arr_val)
		{
			if(count($shipmentpayout_arr) == 0)
			{
				unset($now);
				unset($your_date);
				unset($datediff);
				unset($no_days);
				unset($shipment_date);
				$shipment_date = date('Y-m-d h:i:s', strtotime(trim($comments_arr_val[0]['created_at'])));
				$to_day_date = time();
				$your_date = strtotime($shipment_date);
     			$datediff = ($to_day_date - $your_date);
     			$no_days = floor(($datediff)/(60*60*24));
     			if($no_days<5)
     			{
     				return "<span style='color:#FF64F2;'><b>Waiting to be shipped</b></span>";
     			}
     			else if($no_days>5 && $no_days<=10) {
     				return "<span style='color:#A52A2A;'><b>Delayed</b></span>";
     			}
				else if($no_days>10 && $no_days<=15) {
     				return "<span style='color:#FFA836;'><b>Super Delayed</b></span>";
     			}
				else if($no_days>15) {
     				return "<span style='color:#FF0000;'><b>Need to ESCALATE</b></span>";
     			}
			}
			else {
				return "<span style='color:#8ede26;'><b>Shipped</b></span>";
			}										
		}*/
	}
}



/*class Unirgy_Dropship_Block_Adminhtml_Shipment_RenderEscalate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$Row_Id = $row->getId();
		$res = Mage::getSingleton('core/resource');
		$get_shipment_adr_col = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('main_table.entity_id',$Row_Id);
		$get_shipment_adr_col->getSelect()
							->join(array('a'=>$res->getTableName('sales/order_address')), 'a.parent_id=main_table.order_id and a.address_type="shipping"', array('a.country_id'));
		$get_shipment_adr_col_arr = $get_shipment_adr_col->getData();
		$country_id_val = $get_shipment_adr_col_arr[0]['country_id'];
		$order_id_value = $get_shipment_adr_col_arr[0]['order_id'];
		if($country_id_val == "IN")
		{
			$get_shipment_ord_col = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('main_table.order_id',$order_id_value);
			echo "<pre>";
			print_r($get_shipment_ord_col->getData());
			//exit();*/
			
			/*$comments = Mage::getModel('sales/order_shipment_comment')->getCollection()
																->addFieldToFilter('parent_id',$Row_Id)	
																->addAttributeToFilter('udropship_status', array('Received in Craftsvilla'))
																->setOrder('entity_id', 'DESC')->setPage(0, 1);
			$comments_arr = $comments->getData();
			if($comments_arr)
			{
				
			}	*/												
		//}
		/*echo "<pre>AAA:";
		print_r($get_shipment_adr_col_arr);
		exit();*/
	/*}	
}*/