<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$order_month = Mage :: getModel('sales/order')->getCollection()
		->addFieldToFilter('status',array('processing','complete','closed'))
		->addFieldToFilter('created_at',array('from' => '2012-08-01', 'to' => '2012-08-31'));
		
$order_bef = Mage :: getModel('sales/order')->getCollection()
		->addFieldToFilter('status',array('processing','complete','closed'))
		->addFieldToFilter('created_at',array('lteq' => '2012-07-31'));
		
$new_customers = 0;
$total_customers = 0;

foreach($order_month as $_order_month):

	$_order_month_email = $_order_month->getCustomerEmail();
	$_old_customer_bool = FALSE;
	foreach ($order_bef as $_order_bef):
			$_order_bef_email = $_order_bef->getCustomerEmail();
			
				if ($_order_month_email == $_order_bef_email) {
					//echo "Old customer-----".$_order_month_email."<br/>";
					 $_old_customer_bool = TRUE;
					 
					 break;
				}
			
	endforeach;
	
	if (!$_old_customer_bool) {
			//echo "New Customer------".$_order_month_email."<br/>";
			$new_customers++;
                         echo " : ".$new_customers;
	}
	$total_customers++;
				
endforeach;
echo "Total New Customers: ".$new_customers;
echo "Total Customers: ".$total_customers;
$repeat_percentage= ($total_customers-$new_customers)*100/$total_customers;
echo "Repeat %: ".$repeat_percentage."%";

?>
