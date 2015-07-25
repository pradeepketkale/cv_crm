<?php

require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$k = 0;
//$shipprocquery = "SELECT sfo.entity_id,sfo.increment_id,sfo.status,sfop.method FROM sales_flat_order sfo LEFT JOIN sales_flat_order_payment sfop ON sfo.entity_id = sfop.parent_id WHERE sfo.status = 'processing' AND sfop.method = 'secureebs_standard'";
$shipprocquery = "SELECT sfo.entity_id,sfo.increment_id,sfo.status,sfop.method FROM sales_flat_order sfo LEFT JOIN sales_flat_order_payment sfop ON sfo.entity_id = sfop.parent_id WHERE sfo.status = 'processing' AND sfo.created_at > (DATE_SUB(NOW(),INTERVAL 7 DAY))";
$message1 = "
<table border=1><tr bgcolor=#ccc><th>Order_ID
</th></tr>
";
$shipmentdetails = $db->query($shipprocquery)->fetchAll();
//while($result = mysql_fetch_array($shipmentdetails)){
 foreach($shipmentdetails as $result){
$entityid = $result["entity_id"];
$ordernum = $result["increment_id"];
$method = $result["method"];
//echo $entityid."&nbsp;";
//echo $method;
$salesshipment = "SELECT order_id,increment_id FROM sales_flat_shipment WHERE order_id = '".$entityid."' AND created_at > (DATE_SUB(NOW(),INTERVAL 7 DAY))";

$shipments = $db->query($salesshipment);
$resshipment = $shipments->fetch(); 
$incrementid = $resshipment["increment_id"];
//echo $incrementid."</br>";
//$orderid = $resshipment["order_id"];
//echo $orderid."</br>";
$orders = "Below Orders Shipments Not Created But Order in Processing Status:<br>";
if($incrementid == null)
	{
		$message2 .= "
		<tr><td>".$ordernum."
		</td></tr>
		";
		$k++;
	}
	
}
$orders = $message1.$message2."</table>";
echo "Sending Email:";
$mail = Mage::getModel('core/email');
					
					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($orders);
					$mail->setSubject('Orders For Shipments Not Created:'.$k);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Places");
					$mail->setType('html');
					//$mailerror = $mail->send();
					//print_r($mailerror); exit;

					if($mail->send())
	{
	echo "Email sent to your emailid sucessfully";
	}
	else {
	echo "Failed to send Email";
	}
?>

