<?php //exit;
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Agent shipment Payout Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();


$con = Mage::getSingleton('core/resource')->getConnection('core_read');

if($con)
{
    // $sql         = "select increment_id, order_increment_id from sales_flat_shipment_grid";
    	//$sql         = "select * from sales_flat_shipment as sfs WHERE `agent_id`  `created_at` between DATE_SUB(NOW(),INTERVAL 15 DAY) AND NOW()";
		$sql = "select * from sales_flat_shipment as sfs WHERE `agent_id` IS NOT NULL and `created_at` between DATE_SUB(NOW(),INTERVAL 120 DAY) AND NOW()";
		$results     = $con->query($sql)->fetchAll();
echo "Number of Agent Shipments:".mysql_num_rows($results); 
    
    if($results && mysql_num_rows($results))
    {
        //while ($row = mysql_fetch_array($results)) {
 	foreach($results as $row){

			echo 'Shipment Number: '.$row['increment_id'];
			$shipmentOrder = Mage::getModel('sales/order')->load($row['order_id']);
			unset($total_amount);
		    unset($commission_amount);
		    unset($agent_amount);
			unset($itemised_total_shippingcost);
			echo $total_amount = $row['base_total_value'];
			$disCouponcode = '';
			$discountAgentCoupon = 0;
			echo $itemised_total_shippingcost = $row['itemised_total_shippingcost'];
		    $agentId = $row['agent_id'];
			$getDiscountQuery = "SELECT SUM(`base_discount_amount`) as base_discount_amount  FROM `sales_flat_shipment_item` WHERE `parent_id` = '".$row['entity_id']."'";
			$discountAmountResult = $con->query($getDiscountQuery)->fetchAll();
			//while($row1 = mysql_fetch_array($discountAmountResult)){
			foreach($discountAmountResult as $row1){
				$baseDiscountAmount1 = $row1['base_discount_amount'];
				}
			
			//Below line is for get closingBalance
				$collectionAgent = Mage::getModel('uagent/uagent')->load($agentId);
				$closingbalance = $collectionAgent->getClosingBalance();
			echo "Commission Percent:";
		    	 echo $commission_percentage = $collectionAgent->getAgentCommission();
			//	$commission_percentage = 20;
				
				// For get the Value of coupon id & agentid
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($shipmentOrder->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponAgentId = $_resultCoupon->getAgentid();
		    	
				if($couponAgentId == $agentId)
				{
					echo "Agent Coupon Used and Discount percent is ";
					echo $discountAgentCoupon = $_resultCoupon->getDiscountAmount();
					$baseDiscountAmount =0;
					$disCouponcode = $shipmentOrder->getCouponCode();
				}
				else
					{
					echo "Agent Coupon Not Used and Discount is Rs ";
					$discountAgentCoupon = 0;
					echo $baseDiscountAmount = $baseDiscountAmount1;
					}
		echo "Agent Amount";		
		echo $agent_amount = (($total_amount+$itemised_total_shippingcost-$baseDiscountAmount)*($commission_percentage-$discountAgentCoupon)/100);
						
				
				//Below lines for to update the value in shipmentpayout table ...
					$write = Mage::getSingleton('core/resource')->getConnection('agentpayout_write');
		if(($agent_amount) >= 0)
			{
			echo "Closing Balance: ";
			echo $closingbalance = $closingbalance + $agent_amount; 
			$sql2 = $con->query("select * from agentpayout where shipment_id = '".$row['increment_id']."'")->fetch();
				if(mysql_num_rows($sql2)==0)
					{
						echo "<br />NUMBER:".mysql_num_rows($sql2);
						$sql3 = "insert into agentpayout (shipment_id,agentpayout_created_time,payment_amount,couponcode,agent_commission) values('".$row['increment_id']."',NOW(),'".$agent_amount."','".$disCouponcode."','".$commission_percentage."')";
						$results3     = $con->query($sql3)->fetch();
						$queyAgent = "update `uagent` set `closing_balance` = '".$closingbalance."' WHERE `agent_id` = '".$agentId."'";
						$write->query($queyAgent)->fetch();	
						echo ":inserted row, agent pay amount and closing balance".$row['increment_id']."<br/>";
					}    
					else
					{
						echo ":Skipped row for".$row['increment_id']."<br/>";
					}
			}
			
		}
	}
}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Agent Shipment Payout Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>
