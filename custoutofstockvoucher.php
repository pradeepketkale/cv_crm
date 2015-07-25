<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$shipments="select sa.`entity_id`, sa.`order_id`, od.`base_discount_amount`, sa.`increment_id`, od.`customer_email`, od.`customer_firstname`, od.`customer_lastname`, sa.`base_total_value`, sa.`itemised_total_shippingcost`, sa.`total_qty`,pa.`method` from `sales_flat_shipment` as sa, `sales_flat_order` as od,`sales_flat_order_payment` as pa where sa.`udropship_status`=18 AND od.`entity_id`=sa.`order_id` AND od.`entity_id`= pa.`parent_id` AND pa.`method` != 'cashondelivery' AND sa.`updated_at` < DATE_SUB(NOW(), INTERVAL 12 HOUR)";

$primary=$db->query($shipments)->fetchAll();
$discountoff = '5%';

//while($primaryres = mysql_fetch_array($primary))
foreach($primary as $primaryres)
{
	$orderid = $primaryres['order_id'];
	$orderaddress = "select `country_id` from `sales_flat_order_address` where `parent_id`= '".$orderid."' AND `address_type` = 'shipping'";
	$orderaddressres =$db->query($orderaddress)->fetchAll();
	//while($orderaddressresval = mysql_fetch_array($orderaddressres))
	foreach($orderaddressres as $orderaddressresval)
    {
		$coutryid = $orderaddressresval['country_id'];
	}
	if($coutryid == 'IN')
	{
	$entityid = $primaryres['entity_id'];
	$custemail = $primaryres['customer_email'];
	$primaryres['customer_firstname'].' '.$primaryres['customer_lastname'];
	$incrementid = $primaryres['increment_id'];
	$grandprice = floor($primaryres['base_total_value']);
	$itemised = $primaryres['itemised_total_shippingcost'];
	//$discountamount = $primaryres['base_discount_amount'];
	$totalqty = $primaryres['total_qty'];
	$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
			
			$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$entityid)
								  ->columns('SUM(a.base_discount_amount) AS amount');
			$orderitemdata = $orderitem->getData();
			foreach($orderitemdata as $_orderitemdata)
			{
			  $discountamount = floor(($_orderitemdata['amount']));
			
			}
	//$totalcost = $itemised*$totalqty;
	$totalcost = floor($itemised);
	$grandtotal = $grandprice + $totalcost - $discountamount;
	$xtravalue = floor(($grandtotal*0.05));
	$couponvalue = floor($grandtotal*(1.05));
	$couponCode='CVRF'.generateUniqueId(4).substr($incrementid, -5);
	
$enddate = 'SELECT Date_add(NOW(), interval 90 DAY)';
$date=$db->query($enddate)->fetchAll();
//while($dateres = mysql_fetch_array($date))
foreach($date as $dateres)
{
	$endofdate = $dateres[0];
}

$rule = Mage::getModel('salesrule/rule');
$customer_groups = array(0, 1, 2, 3);
 $rule->setName('Customer refund given by system of Rs. '.$couponvalue.' for shipment '.$incrementid)
      ->setDescription('Customer refund given by system of Rs. '.$couponvalue.' for shipment '.$incrementid)
      ->setFromDate(NOW())
	  ->setToDate($endofdate)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCoupon(1)
      ->setUsesPerCustomer(1)
      ->setCustomerGroupIds($customer_groups) 
      ->setIsActive(0)
      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('cart_fixed')
      ->setDiscountAmount($couponvalue)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('1')
      ->setIsRss(0)
      ->setVendorid('')
	  ->setWebsiteIds(array(1));
	  
/* $item = Mage::getModel('salesrule/rule_condition_product_subselect')
      ->setType('salesrule/rule_condition_product_subselect')
	  ->setAttribute('base_row_total')
	  ->setOperator('>=')
	  ->setValue($couponvalue)
      ->setAggregator('all');*/
	  
	  
  /*  $rule->getConditions()->addCondition($item);*/
   /* $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $item->addCondition($conditions);
 
    $actions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $rule->getActions()->addCondition($actions);*/
  $rule->save();
  $shipmentvalue = "<table border='0' width='auto'><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Shipment Value: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$grandprice."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Discount Amount: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. -".$discountamount."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShippingCost: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$totalcost."</td></tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Extra 5% Value: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$xtravalue."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><strong>Total Coupon Value:</strong> </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><strong>Rs. ".$couponvalue."</strong></td></tr></table>";
$ShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
$_items1 = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
				$all = $_items1->getAllItems();
				foreach ($all as $_item)
				{
					
				$product = Mage::getModel('catalog/product')->load($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
				 $ShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incrementid."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".floor($_item->getPrice())."</td></tr>";
				}
		$ShipmentItemHtml .= "</table>";	
	$av1 = 'Accept';
	$av2 = 'Not Accept';
	$urlaction1 = Mage::getBaseUrl().'umicrosite/vendor/custvoucheraccept?q='.$incrementid.'&voucher='.$couponCode.'&action='.$av1;
	$urlaction2 = Mage::getBaseUrl().'umicrosite/vendor/custvoucheraccept?q='.$incrementid.'&voucher='.$couponCode.'&action='.$av2;
	 $storeId = Mage::app()->getStore()->getId();
						$templateId = 'voucher_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
						//$translate  = Mage::getSingleton('core/translate');
						//$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Refund Voucher For Shipment No: '.$incrementid.'!';
						$voucherHtml = '';
				$voucherHtml = '<a href ="'.$urlaction1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="accept" value="Accept">Accept</button></a>';
			$voucherHtml .= '<a href ="'.$urlaction2.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="accept" value="Not Accept" >Not Accept</button></a>';		            
					$vars = Array('voucherHtml' =>$voucherHtml, 'shipmentid' => $incrementid, 'voucher'=>$couponCode, 'couponvalue'=>$couponvalue, 'expirydate'=>$endofdate, 'ShipmentItemHtml' =>$ShipmentItemHtml, 'shipmentvalue' => $shipmentvalue);		
								
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $custemail, $recname, $vars, $storeId);
						//$translate->setTranslateInline(true);
						$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $recname, $vars, $storeId);
						//$translate->setTranslateInline(true);
						echo "Email has been sent successfully";
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$productquery = "update `sales_flat_shipment` set `udropship_status` = 13 WHERE `increment_id`= ".$incrementid;
		$writequery = $write->query($productquery)->fetch();	
}
}
function generateUniqueId($length = null)
			{
				$rndId = crypt(uniqid(rand(),1));
				$rndId = strip_tags(stripslashes($rndId));
				$rndId = str_replace(array(".", "$"),"",$rndId);
				$rndId = strrev(str_replace("/","",$rndId));
					if (!is_null($rndId)){
					return strtoupper(substr($rndId, 0, $length));
					}
				return strtoupper($rndId);
			}


?>
