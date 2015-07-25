<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$order = Mage::getModel('sales/order')->getCollection();
		   $order->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
				//->join(array('b'=>'ebslink'), 'main_table.increment_id=b.order_no', array('ebslink_id','order_no', 'ebslinkurl', 'comment', 'created_time'))
				->joinLeft('sales_flat_order_payment', 'main_table.entity_id = sales_flat_order_payment.parent_id','method')
				->where('(main_table.status = "pending" OR main_table.status = "holded" OR main_table.status = "canceled") AND 
					((main_table.created_at < (NOW() - INTERVAL 1 DAY)) AND (main_table.created_at > (NOW() - INTERVAL 2 DAY))) AND 
					sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","payucheckout_shared","paypal_standard") AND 
					a.address_type = "billing" AND
					 main_table.base_grand_total > 500 ');
				//->where('main_table.increment_id = "100016606"');
//echo "Query:".$order->getSelect()->__toString();exit();

		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
$collectionOrderQuery = "SELECT `customer_email` FROM `sales_flat_order` WHERE sales_flat_order.`status` = 'processing' AND sales_flat_order.`created_at` > DATE_SUB(NOW(),INTERVAL 3 DAY)";
                $collectionOrder = $read->query($collectionOrderQuery)->fetchAll();
                $orderEmail = Array();
                foreach ($collectionOrder as $itemOrder) {
                        $orderEmail[] = $itemOrder['customer_email'];
                }

$ebsorder = $order->getData();
echo "Total To Send";
echo sizeof($ebsorder); 


foreach($ebsorder as $_ebsorder)
	{
		$custEmail = $_ebsorder['customer_email'];
                $incrementid = $_ebsorder['increment_id'];
		$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
				
		if($readEbslink && (!in_array($custEmail,$orderEmail))) {
			echo 'Sending Reminder Ebslink Email & SMS For Customer:'.$custEmail;
			try{
				Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
			}
			catch(Exception $e)
			{
				echo "Error in Sending";
			}
			
		}
		else
		{
			echo "Skipped Reminder Email For Customer:".$custEmail;
		}
				
	}
